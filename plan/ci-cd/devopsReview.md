# DevOps Review

## Scope

Reviewed files:

- `.github/workflows/ci.yml`
- `.dockerignore`
- `.env.example.docker`
- `Dockerfile.dev`
- `Dockerfile.prod`
- `docker-compose.yml`
- `docker-entrypoint.sh`
- `nginx/default.conf`
- `seccomp-profile.json`
- `plan/ci-cd/**`

## Executive Summary

The project has a strong first pass at Docker and CI/CD hardening: base images are pinned, containers run as non-root, Docker secrets are introduced, Trivy scanning exists, file permissions are tighter, and CI runs Pint, PHPStan, tests, and a Docker image scan.

The current implementation is not production-ready yet. The largest risks are Docker runtime correctness, incomplete image scanning, an Nginx/PHP-FPM mismatch, and a seccomp profile that may weaken Docker's default sandbox because it uses a broad allow-by-default model.

## Priority Findings

### Critical

1. **Runtime PHP images do not install required PHP extensions** - Fixed

   `Dockerfile.prod` and `Dockerfile.dev` previously installed PHP extensions only in the `builder` stage. The final PHP runtime stages started again from fresh `php:8.4-fpm` / `php:8.4-cli` images and only copied the application code.

   Impact:
   - Runtime containers may miss `pdo_mysql`, `zip`, `mbstring`, `exif`, `pcntl`, `bcmath`, and `gd`.
   - Composer can succeed in the builder while the actual app fails at runtime.

   Recommended fix:
   - Done: both Dockerfiles now use a shared `php-base` stage with the required PHP extensions.
   - Done: the builder and runtime stages inherit from `php-base`, avoiding extension drift.

2. **`Dockerfile.prod` default build target is Nginx only** - Fixed

   The final stage in `Dockerfile.prod` is `nginx-runtime`, so `docker build -f Dockerfile.prod .` produces only the Nginx image by default.

   Impact:
   - CI currently scans only the Nginx image, not the PHP-FPM application image.
   - A deployment using the default image would not contain PHP-FPM.

   Recommended fix:
   - Done: CI builds and scans both targets explicitly:
     - `docker build --target php-runtime -t laravel-app-php-ci -f Dockerfile.prod .`
     - `docker build --target nginx-runtime -t laravel-app-nginx-ci -f Dockerfile.prod .`
   - Done: both images are scanned with Trivy.
   - Done: `docker-compose.prod.yml` shows both production services.

3. **Nginx FastCGI configuration does not match PHP-FPM runtime**

   `nginx/default.conf` uses:

   ```nginx
   fastcgi_pass app:8000;
   ```

   But the production PHP-FPM stage exposes port `9000`.

   Impact:
   - Production Nginx will not reach PHP-FPM correctly.
   - The Nginx health check may return 502 when PHP routes are requested.

   Recommended fix:
   - Use `fastcgi_pass app:9000;` for a PHP-FPM service named `app`.
   - Add a production compose file with `nginx` and `app` services on the same network.

4. **Nginx runs as non-root but listens on port 80**

   The Nginx runtime switches to `USER laravel` and still uses `listen 80`.

   Impact:
   - Non-root Nginx may fail to bind privileged port 80.
   - It may also fail to write PID/temp files unless Nginx runtime directories are writable.

   Recommended fix:
   - Change Nginx to listen on `8080`, expose `8080`, and map host port 80 externally.
   - Configure writable temp/PID paths or provide tmpfs mounts in production.

### High

5. **Custom seccomp profile is allow-by-default**

   `seccomp-profile.json` uses:

   ```json
   "defaultAction": "SCMP_ACT_ALLOW"
   ```

   Impact:
   - Applying this profile overrides Docker's default seccomp profile.
   - Because it only blocks a small denylist, it may allow more syscalls than Docker's default profile.

   Recommended fix:
   - Start from Docker's official default seccomp profile and remove/adjust only what the app needs.
   - Keep `defaultAction` restrictive where practical.
   - Test with `docker compose up`, HTTP requests, DB operations, queue/cache/session writes, and shutdown.

6. **CI Docker scanning does not cover all built images**

   Current CI builds a single default image:

   ```yaml
   docker build -t laravel-app-ci -f Dockerfile.prod .
   ```

   Because the default target is Nginx, PHP runtime vulnerabilities are missed.

   Recommended fix:
   - Build and scan `php-runtime` and `nginx-runtime` separately.
   - Tag images with commit SHA.
   - Use Trivy cache to reduce runtime.

7. **Docker Content Trust is brittle in this workflow**

   `DOCKER_CONTENT_TRUST=1` is enabled only for the Docker build step.

   Risk:
   - Docker Content Trust can fail when upstream images do not publish Notary metadata.
   - Digest pinning already provides stronger reproducibility for base images.

   Recommended fix:
   - Keep digest pinning.
   - Consider signing produced images with Sigstore/Cosign after pushing to GHCR.
   - Add SBOM generation using Trivy or Syft.

8. **Development Dockerfile installs production Composer dependencies only**

   `Dockerfile.dev` runs:

   ```dockerfile
   composer install --no-dev
   ```

   Impact:
   - Development/test tools like Pest, Pint, Larastan, and Laravel Boost are unavailable inside the dev image.
   - This reduces usefulness of the dev container.

   Recommended fix:
   - In `Dockerfile.dev`, install dev dependencies:
     - `composer install --no-interaction --prefer-dist`
   - Keep `--no-dev` only in production.

9. **Local Compose app bind mount can hide image-built dependencies**

   `docker-compose.yml` mounts:

   ```yaml
   - .:/var/www/html:ro
   ```

   Impact:
   - The host project hides files built into the image at `/var/www/html`.
   - If the host does not have `vendor/` or `public/build/`, the dev container may fail despite the image containing them.

   Recommended fix:
   - Either require local `composer install` / `npm run build`, or add named volumes for `/var/www/html/vendor` and `/var/www/html/public/build`.
   - For a real dev container, consider a writable source mount and a separate non-production security profile.

### Medium

10. **Composer install cache is inefficient**

   Copying the full app before `composer install` fixes `artisan package:discover`, but it makes Composer cache invalidated by almost any source change.

   Recommended fix:
   - Use this pattern:

     ```dockerfile
     COPY composer.json composer.lock ./
     RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts
     COPY . ./
     RUN composer dump-autoload --optimize \
         && php artisan package:discover --ansi
     ```

11. **Nginx health check depends on application availability**

   The Nginx health check calls `/`, which can fail due to PHP-FPM, database, app key, or route-level exceptions.

   Recommended fix:
   - Add a lightweight `/health` route in Laravel.
   - For Nginx-only liveness, add a static `/healthz` file served without PHP.
   - Use readiness checks separately from liveness checks.

12. **PHP-FPM health check does not validate PHP-FPM**

   `php -r "echo 'ok';"` only proves PHP CLI runs. It does not prove PHP-FPM is accepting requests.

   Recommended fix:
   - Configure PHP-FPM status/ping endpoint, or use a lightweight FastCGI health check.

13. **Trivy scanner image is not pinned**

   CI uses:

   ```bash
   docker run --rm aquasec/trivy ...
   ```

   Recommended fix:
   - Pin Trivy to a versioned tag or digest.
   - Consider `aquasecurity/trivy-action` pinned to a stable version.

14. **CI lacks explicit test environment settings**

   CI sets `APP_KEY` but does not set common test-safe Laravel settings.

   Recommended fix:
   - Add:

     ```yaml
     APP_ENV: testing
     DB_CONNECTION: sqlite
     DB_DATABASE: ':memory:'
     CACHE_STORE: array
     SESSION_DRIVER: array
     QUEUE_CONNECTION: sync
     MAIL_MAILER: array
     ```

15. **Docker Compose secrets require manual local files**

   Compose expects:

   - `.docker-secrets/app_key`
   - `.docker-secrets/db_password`
   - `.docker-secrets/db_root_password`

   Recommended fix:
   - Add documented local setup commands in `plan/ci-cd` or README.
   - Do not commit the secret values.

16. **`docker-entrypoint.sh` should be kept LF-only**

   The script is invoked with:

   ```dockerfile
   ENTRYPOINT ["sh", "docker-entrypoint.sh"]
   ```

   Recommended fix:
   - Ensure `.gitattributes` forces LF for shell scripts:

     ```gitattributes
     *.sh text eol=lf
     ```

17. **Nginx production runtime has no production Compose/deployment definition**

   `docker-compose.yml` only builds the dev app service and MySQL.

   Recommended fix:
   - Add `docker-compose.prod.yml` with:
     - `app` from `Dockerfile.prod --target php-runtime`
     - `nginx` from `Dockerfile.prod --target nginx-runtime`
     - shared network
     - secret mounts
     - health checks

### Low

18. **`version: '3.9'` is obsolete in modern Compose**

   Docker Compose v2 no longer requires the top-level `version` key.

   Recommended fix:
   - Remove it when Compose validation is available.

19. **Plan documents are partially stale**

   Some checklists still show planned work that is now implemented, while other implemented items are not reflected in the high-level task plan.

   Recommended fix:
   - Update `plan/ci-cd/tasks.md`, `docker-plan.md`, and `ci-cd-plan.md` after the Docker/CI implementation stabilizes.

20. **Image metadata is missing**

   Dockerfiles do not include OCI labels.

   Recommended fix:
   - Add labels such as:
     - `org.opencontainers.image.title`
     - `org.opencontainers.image.source`
     - `org.opencontainers.image.revision`

## Positive Findings

- Base images are pinned to SHA256 digests.
- Runtime users are non-root.
- App file permissions are generally restricted to `755` directories and `644` files.
- Laravel writable paths are scoped to storage/cache locations.
- `.dockerignore` excludes secrets, VCS metadata, dependencies, and local-only files.
- Docker secrets are used instead of plaintext DB passwords.
- Resource limits are present in Compose.
- `no-new-privileges` is enabled.
- CI runs formatter, static analysis, tests, Docker build, and image scanning.
- Vite is disabled in tests, so tests do not depend on frontend manifests.

## Recommended Next Implementation Order

1. Fix PHP runtime extensions in final runtime stages.
2. Fix production Nginx/PHP-FPM wiring: `fastcgi_pass`, port, non-root Nginx runtime paths.
3. Build and scan both `php-runtime` and `nginx-runtime` targets in CI.
4. Replace the allow-by-default seccomp profile with Docker-default-derived profile.
5. Split local development Compose from hardened production Compose.
6. Improve Composer Docker caching with `--no-scripts` then explicit package discovery.
7. Add explicit CI test environment variables.
8. Document local Docker secret creation and production deployment flow.

## Suggested CI Docker Job Shape

```yaml
docker-scan:
  needs: test
  runs-on: ubuntu-latest
  steps:
    - uses: actions/checkout@v4

    - name: Setup Docker Buildx
      uses: docker/setup-buildx-action@v4

    - name: Build PHP runtime image
      run: docker build --target php-runtime -t laravel-app-php:${{ github.sha }} -f Dockerfile.prod .

    - name: Build Nginx runtime image
      run: docker build --target nginx-runtime -t laravel-app-nginx:${{ github.sha }} -f Dockerfile.prod .

    - name: Scan PHP runtime image
      run: docker run --rm -v /var/run/docker.sock:/var/run/docker.sock aquasec/trivy image --exit-code 1 --severity CRITICAL,HIGH laravel-app-php:${{ github.sha }}

    - name: Scan Nginx runtime image
      run: docker run --rm -v /var/run/docker.sock:/var/run/docker.sock aquasec/trivy image --exit-code 1 --severity CRITICAL,HIGH laravel-app-nginx:${{ github.sha }}
```

## Final Assessment

Current state: good learning implementation with meaningful security controls, but not ready for production deployment.

Primary blocker: the Docker production topology is incomplete and CI currently does not prove that the PHP runtime image works or is secure.

The next milestone should be a working production Compose/deployment definition that starts both PHP-FPM and Nginx, validates health checks, and scans every deployable image.
