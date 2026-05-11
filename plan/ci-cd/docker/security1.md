# Docker Security Analysis and Fixes

## Overview

This document analyzes the security of the Dockerfiles (`Dockerfile.dev` and `Dockerfile.prod`) and provides detailed solutions for each identified issue. The analysis covers container security best practices including user permissions, image vulnerabilities, health checks, and resource management.

## Security Issues and Solutions

### 1. Base Images Not Pinned to Specific SHAs

**Issue**: Using tags like `node:20-alpine` allows automatic updates that could introduce vulnerabilities.

**Risk**: Supply chain attacks if base images are compromised or updated with vulnerable versions.

**Solution**:
- Pin base images to specific SHA256 hashes for reproducible and secure builds.
- Check image SHAs using `docker inspect <image>` or Docker Hub/GitHub Container Registry.
- Update SHAs periodically after security reviews.

**Example Fix**:
```dockerfile
FROM node:20-alpine@sha256:1234567890abcdef... AS node-builder
FROM php:8.4-cli@sha256:abcdef1234567890... AS builder
```

### 2. Health Check Commands May Not Work

**Issue**: Health check commands reference tools or endpoints that don't exist.

**Risk**: Containers appear healthy when they're not, leading to undetected failures.

**Solutions by Dockerfile**:

**Dockerfile.dev**:
- Current: `CMD php -r "file_get_contents('http://localhost:8000/health')"`
- Problem: `/health` endpoint doesn't exist in Laravel by default.
- Fix: Install curl and check the root endpoint or create a health route.

**Dockerfile.prod (PHP-FPM)**:
- Current: `CMD php-fpm-healthcheck`
- Problem: `php-fpm-healthcheck` command doesn't exist.
- Fix: Use a simple PHP check or install a health check tool.

**Dockerfile.prod (Nginx)**:
- Current: `CMD wget --quiet --tries=1 --spider http://localhost/health`
- Problem: `wget` not installed in nginx:stable-alpine.
- Fix: Install curl or wget, or use a different check method.

### 3. Overly Permissive File Permissions

**Issue**: `chmod -R 775 storage bootstrap/cache` makes files group-writable.

**Risk**: If the group is compromised, files can be modified.

**Solution**:
- Use more restrictive permissions: 755 for directories, 644 for files.
- Only make specific directories writable if needed (e.g., storage/logs).
- Consider using bind mounts for persistent data instead of container permissions.

**Example Fix**:
```dockerfile
RUN chown -R laravel:laravel /var/www/html \
    && chmod -R 755 storage bootstrap/cache \
    && chmod -R 775 storage/logs storage/framework/cache
```

### 4. No Image Vulnerability Scanning

**Issue**: No automated scanning for known vulnerabilities in built images.

**Risk**: Unknown CVEs in dependencies could be exploited.

**Solution**:
- Integrate vulnerability scanning tools in CI/CD pipeline.
- Use tools like Trivy, Clair, Docker Scout, or Snyk.
- Scan images before deployment and fail builds on high-severity vulnerabilities.

**Example Tools**:
- Trivy: `trivy image myapp:latest`
- Docker Scout: `docker scout cves myapp:latest`

### 5. Git Installed in Builder Stages

**Issue**: `git` is installed in PHP builder stages.

**Risk**: Could be used for attacks if container is compromised (though builder stages are discarded).

**Solution**:
- Remove `git` from the RUN command if not needed.
- If git is required for composer installs, keep it but ensure it's not in final runtime images.
- Use `--no-install-recommends` and clean up properly.

**Fix**: Remove `git \` from the apt-get install line if not needed.

### 6. No Resource Limits

**Issue**: No CPU/memory limits set in Dockerfiles.

**Risk**: Containers could consume unlimited resources, affecting host system.

**Solution**:
- Set resource limits in docker-compose.yml or Kubernetes manifests.
- Use Docker flags: `--memory`, `--cpus`, `--memory-swap`, etc.
- Monitor resource usage and adjust limits based on application needs.

**Example in docker-compose.yml**:
```yaml
services:
  app:
    deploy:
      resources:
        limits:
          memory: 512M
          cpus: '0.5'
```

### 7. Potential .env Exposure

**Issue**: Environment files could be copied accidentally.

**Risk**: Secrets exposure if .env files contain sensitive data.

**Solution**:
- Ensure `.dockerignore` excludes `.env*` files.
- Use Docker secrets or external secret management.
- Never copy .env files in Dockerfiles.

**Verification**: Check that `.dockerignore` contains `.env*`.

### 8. No Seccomp or AppArmor Profiles

**Issue**: No syscall restrictions on containers.

**Risk**: Malicious code could make unauthorized system calls.

**Solution**:
- Apply seccomp profiles to limit syscalls.
- Use AppArmor or SELinux profiles.
- Docker provides default seccomp profile; customize for application needs.

**Example**:
```bash
docker run --security-opt seccomp:profile.json myapp
```

### 9. Runtime Container May Still Run as Root

**Issue**: Some runtime stages or services may still run as root, even when a non-root user is created.

**Risk**: Root processes inside containers increase the impact of a container compromise.

**Solution**:
- Ensure the final runtime stage includes `USER laravel` after creating and configuring the non-root user.
- Verify Nginx and PHP-FPM runtime stages both switch to the non-root user.
- Do not rely only on file ownership; the runtime process must execute as non-root.

**Example**:
```dockerfile
USER laravel
```

## Security Checklist

### Image Security
- [x] **Pin base images to SHA256 hashes**
  - Get SHA256 for each base image using `docker pull <image> && docker inspect <image> | grep RepoDigests`
  - Update Dockerfiles to use `@sha256:...` format
  - Document SHA updates in security reviews

- [x] **Implement vulnerability scanning**
  - Install Trivy or Docker Scout in CI/CD pipeline
  - Add scanning step after image build
  - Set policy to fail builds on critical/high vulnerabilities
  - Review and update dependencies regularly

### Runtime Security
- [x] **Fix health checks**
  - For Dockerfile.dev: Install curl and check root endpoint
  - For Dockerfile.prod PHP: Use `php -r "echo 'ok';"` or install healthcheck tool
  - For Dockerfile.prod Nginx: Install curl and check endpoint
  - Test health checks manually: `docker run --rm myimage` and check exit codes

- [x] **Ensure runtime containers do not run as root**
  - Verify `USER laravel` is present in final runtime stages
  - Check Nginx and PHP-FPM stages separately for non-root execution
  - Confirm file ownership and runtime user both use `laravel`

- [x] **Restrict file permissions**
  - Change `chmod -R 775` to `chmod -R 755`
  - Make only necessary directories writable (logs, cache)
  - Use bind mounts for persistent data when possible

- [x] **Add resource limits**
  - Set memory and CPU limits in docker-compose.yml
  - Monitor usage with `docker stats`
  - Adjust limits based on load testing results

### Configuration Security
- [x] **Verify .dockerignore exclusions**
  - Ensure `.env*`, `.git`, `tests/`, `docs/` are excluded
  - Add any additional sensitive files/patterns
  - Test with `docker build --no-cache .` to verify exclusions

- [x] **Implement secrets management**
  - Use Docker secrets or external providers (AWS Secrets, Vault)
  - Avoid passing secrets via environment variables
  - Rotate secrets regularly

### Advanced Security
- [x] **Apply seccomp profiles**
  - Create custom seccomp profile for application needs
  - Test profile doesn't break application functionality
  - Apply profile in production deployments

- [x] **Remove unnecessary packages**
  - Audit installed packages in builder stages
  - Remove development tools from runtime images
  - Use multi-stage builds effectively

- [x] **Enable security features**
  - Use `--read-only` for immutable containers where possible
  - Implement user namespaces with `--userns-remap`
  - Enable Docker Content Trust for image verification

## Testing Security Fixes

After implementing fixes:

1. **Build and test images**: Ensure applications still work
2. **Run security scans**: Verify vulnerabilities are addressed
3. **Test health checks**: Confirm they pass/fail appropriately
4. **Check permissions**: Verify file access works as expected
5. **Monitor resources**: Ensure limits don't break functionality

## Tools and Resources

- **Trivy**: `https://github.com/aquasecurity/trivy`
- **Docker Scout**: `https://docs.docker.com/scout/`
- **Seccomp profiles**: `https://docs.docker.com/engine/security/seccomp/`
- **OWASP Docker Security**: `https://owasp.org/www-project-docker-security/`

## Debug Container Guidance

Creating a separate debug container is a good practice for troubleshooting container issues without changing your production image.

### Why this is useful
- Keeps production image clean and minimal
- Lets you add debugging tools only when needed
- Allows interactive troubleshooting with the same environment
- Prevents accidental changes to the production build

### How to use a debug container
1. Use the same base image or a similar build stage as your application image.
2. Add debugging tools only in the debug image, such as `bash`, `curl`, `htop`, and `composer`.
3. Mount the source code as a volume for live inspection and edits.
4. Run an interactive shell: `docker run -it --rm --entrypoint sh myapp-debug`.

### Example pattern
- `Dockerfile.dev` or `Dockerfile.prod` remains unchanged.
- Create `Dockerfile.debug` with extra debugging tools.
- Use `docker-compose.debug.yml` or `docker build -f Dockerfile.debug`.

### Security note
- Do not use the debug container in production.
- Keep debug tooling out of the production image.
- Use debug containers only for local troubleshooting and diagnostics.

This checklist provides a comprehensive approach to securing Docker containers for production use.
