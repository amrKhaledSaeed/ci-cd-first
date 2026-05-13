# Docker Maintainability Guide

## Overview

This document explains how to keep Docker files and container setup maintainable. Maintainability means the project is easy to understand, change, and fix over time.

This guide covers:
- Dockerfile structure
- Reusable build stages
- Development vs production images
- Health checks and runtime configuration
- Debug container practices
- Labels, build arguments, and metadata
- Checklist tasks for maintainability

## 1. Keep Dockerfiles Simple and Clear

A maintainable Dockerfile is easy to read and has a clear purpose.

### Why it matters
- Clear Dockerfiles are easier to update later.
- New team members can understand the build quickly.
- Fewer mistakes happen when the file is organized.

### How to do it
- Use comments to explain each stage.
- Separate build stages logically: node, PHP build, runtime.
- Avoid bundling development-only tools into production images.
- Use multi-stage builds so the final image only contains what is needed.

### Example
- `Dockerfile.dev` is for local development.
- `Dockerfile.prod` is for production deployment.

This separation keeps each file focused and easier to maintain.

## 2. Use Multi-Stage Builds

Multi-stage builds let you build assets in one stage and copy only the result to the final image.

### Why it matters
- Reduces final image size
- Removes build tools from runtime images
- Makes it easier to reason about what is in each image

### How to do it
- Build JS assets in a Node stage.
- Build PHP dependencies in a PHP stage.
- Copy only the compiled assets and app files into the final runtime stage.

### Benefit
If a dependency changes, you only need to update the relevant stage instead of the whole file.

## 3. Separate Development and Production Images

Maintaining separate Dockerfiles for dev and prod is a strong practice.

### Why it matters
- Development images can include debugging tools and source mounts.
- Production images stay small, secure, and stable.
- It is easier to test locally without risking production behavior.

### How to do it
- `Dockerfile.dev`: use `php:8.4-cli` and `php artisan serve`.
- `Dockerfile.prod`: use `php:8.4-fpm` and a separate Nginx stage.

### Benefit
You can change the development workflow without affecting production deployment.

## 4. Use WORKDIR Correctly

`WORKDIR` sets the working directory for later commands.

### Why it matters
- Ensures commands run in the right folder
- Avoids repeated `cd` commands
- Makes the Dockerfile easier to understand

### Best practice
- Use `WORKDIR /app` for the Node stage.
- Use `WORKDIR /var/www/html` for PHP stages.

## 5. Use Build Arguments and Labels

`ARG` and `LABEL` add flexibility and metadata to Docker images.

### What is missing in the current files
- There are no `ARG` instructions currently, so you are not using build-time arguments yet.
- There are also no OCI-style `LABEL` entries in either Dockerfile.

### `ARG`
- Build-time variables for versions or flags
- Example: `ARG NODE_VERSION=20`
- Helps avoid hardcoding values

### `LABEL`
- Stores metadata like maintainer, version, and description
- Example:
  ```dockerfile
  LABEL maintainer="team@example.com"
  LABEL version="1.0"
  ```

### Why it matters
- Easier to update shared values
- Better image documentation
- Helps with automation and auditing

## 6. Keep Health Checks Accurate

Health checks help Docker identify when a container is healthy or broken.

### Why it matters
- Avoids traffic being sent to bad containers
- Helps orchestration systems restart unhealthy containers

### Common issues
- Checking endpoints that don't exist
- Using commands not installed in the image

### How to fix
- Use a real route or a lightweight check command
- Install `curl` or `wget` if needed for checks
- Verify the health endpoint exists in Laravel

## 7. Handle Signals Gracefully

Containers should use exec-form commands so the main process receives shutdown signals directly.

### Why it matters
- Docker sends `SIGTERM` to PID 1 when stopping a container
- The process needs to receive the signal to shut down cleanly
- Proper handling avoids abrupt termination and resource leaks

### How to do it
- Use exec-form `CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]` in dev
- Use exec-form `CMD ["php-fpm"]` and `CMD ["nginx", "-g", "daemon off;"]` in prod
- Add custom shutdown logic only if your application needs cleanup tasks

### Benefit
- Containers stop cleanly
- The runtime process handles signals directly
- This improves reliability during deployments and restarts

## 8. Separate Debug Containers from Production

A debug container is useful for troubleshooting without changing production images.

### Why it matters
- Keeps production image clean
- Adds debugging tools only when needed
- Prevents accidental production behavior changes

### Best practice
- Use a `Dockerfile.debug` or a debug service in `docker-compose.debug.yml`
- Add `bash`, `curl`, `htop`, or other tools only in the debug image
- Mount source code for live inspection

## 8. Avoid Root at Runtime

Running as a non-root user improves container security and maintainability.

### Why it matters
- Root processes can do more damage if compromised
- Many best practices and platforms expect non-root containers

### How to do it
- Create a user like `laravel` in the runtime stage
- Use `COPY --chown=laravel:laravel` for application files
- Use `USER laravel` in the final stage

## 9. Document and Maintain Environment Configuration

Keep environment variables, `.env` files, and compose configuration easy to manage.

### Why it matters
- Makes local development easier
- Avoids leaking secrets into images
- Prevents secret values from being baked into Docker images
- Helps maintain consistency across environments

### Best practice
- Use `.env.example.docker` as a template
- Load values with `docker-compose` environment variables
- Do not copy actual `.env` files into images
- Never hardcode secrets in Dockerfiles; keep them in secrets management or runtime config

## 10. Maintain a Checklist for Docker Maintenance

Checklists help you keep the Docker setup healthy over time.

### Why it matters
- Prevents forgotten maintenance tasks
- Helps onboard new team members
- Ensures a consistent workflow

## Checklist Tasks

- [X] **Separate dev and prod Dockerfiles**
  - Keep `Dockerfile.dev` for development and `Dockerfile.prod` for production.
  - Verify each file has a clear and distinct purpose.

- [X] **Verify multi-stage builds**
  - Confirm build stages only copy what the final image needs.
  - Check that no build-only tools remain in the runtime stage.

- [X] **Use `WORKDIR` consistently**
  - Ensure each stage sets the correct working directory.
  - Avoid manual `cd` commands.

- [] **Add build args and labels**
  - Add `ARG` for reusable version values or build settings.
  - Add OCI-style `LABEL` metadata for maintainer, version, and description.
  - Verify build-time arguments work by building with `--build-arg`.

- [ ] **Validate health checks**
  - Confirm health checks use existing routes or installed tools.
  - Test each health check manually.

- [ ] **Handle signals gracefully**
  - Use exec-form `CMD` so the main process receives Docker SIGTERM.
  - Verify the runtime process can shut down cleanly.
  - Add custom shutdown logic only if the app needs cleanup.

- [ ] **Create a debug container pattern**
  - Add a `Dockerfile.debug` or debug Compose service.
  - Keep debug tools out of production images.

- [ ] **Ensure non-root runtime execution**
  - Confirm `USER laravel` is present in runtime stages.
  - Verify the container process runs as the non-root user.

- [X] **Use `.dockerignore` correctly**
  - Exclude `.env*`, `.git`, and local build artifacts.
  - Test by building with `--no-cache` and checking the image contents.
  - Ensure no secret literals are present in Dockerfile or build context.

- [X] **Document environment configuration**
  - Use a `.env.example.docker` template.
  - Keep actual secrets out of the repository.

- [ ] **Review and update base image pins regularly**
  - Pin images to digest SHAs if possible.
  - Update SHAs during security or dependency reviews.

- [ ] **Run periodic maintenance checks**
  - Scan images with a vulnerability tool.
  - Review runtime permissions and health checks.

## Learning Notes

This guide is meant to teach how Docker setup can be made maintainable. When you change the Docker configuration, ask:

- Does this change affect production behavior?
- Is this file still easy to understand?
- Are runtime and build environments separated?
- Can I reproduce this build later?

By answering these questions, you keep your Docker setup maintainable and easier to extend over time.
