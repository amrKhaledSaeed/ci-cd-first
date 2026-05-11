# CI/CD and Docker Implementation Tasks

This is a step-by-step checklist to add Docker and CI/CD to the Laravel project. Follow the tasks in order.

## Docker Tasks

- [X] **Initialize Git Repository**
  - If .git folder is missing, run `git init` to create a new Git repository.
  - Expected: .git folder exists, project is tracked by Git.

- [X] **Create .dockerignore File**
  - Create a file named `.dockerignore` in the project root.
  - Add lines: `.git`, `node_modules`, `vendor`, `.env`, `tests`, `docs`, `storage/logs/*`, `storage/framework/cache/*`.
  - Expected: .dockerignore file exists with the listed entries.

- [X] **Create Dockerfile**
  - Create separate Dockerfiles: `Dockerfile.dev` for development and `Dockerfile.prod` for production.
  - Use multi-stage builds with proper security (non-root user, health checks).
  - Dockerfile.dev: Uses php:8.4-cli with php artisan serve.
  - Dockerfile.prod: Uses php:8.4-fpm with separate Nginx stage.
  - Expected: Both Dockerfiles exist with proper multi-stage builds and security configurations.

- [X] **Create docker-compose.yml**
  - Create `docker-compose.yml` in the project root.
  - Define services: app (build from Dockerfile.dev), db (mysql).
  - Set ports, volumes, environment variables, health checks, and networking.
  - Expected: docker-compose.yml exists with proper service configuration.

- [ ] **Test Docker Setup Locally**
  - Run `docker-compose build` to build images.
  - Run `docker-compose up` to start services.
  - Access http://localhost and verify app works.
  - Expected: App runs in containers, database connected.

## CI/CD Tasks

- [ ] **Create GitHub Repository**
  - Push the project to a new GitHub repository.
  - Ensure the repo is public or has Actions enabled.
  - Expected: Project is on GitHub.

- [ ] **Create GitHub Actions Workflow**
  - Create `.github/workflows/ci.yml` file.
  - Add jobs for: checkout, setup PHP, install deps, lint, test, build image, push image.
  - Use Docker Hub or GHCR for image registry.
  - Expected: .github/workflows/ci.yml exists.

- [ ] **Configure Docker Hub Account**
  - Create Docker Hub account if needed.
  - In GitHub repo settings, add secrets: DOCKER_USERNAME, DOCKER_PASSWORD.
  - Expected: Secrets are set in GitHub.

- [ ] **Test CI Pipeline**
  - Push a commit to trigger the workflow.
  - Check Actions tab to see if jobs pass.
  - Fix any errors in the workflow file.
  - Expected: All CI jobs pass successfully.

- [ ] **Add Deployment Stage**
  - Update workflow to include deployment job.
  - Use a simple deploy script or service like Railway/Heroku.
  - For now, just echo deploy commands.
  - Expected: Workflow includes deploy step.

- [ ] **Test Full Pipeline**
  - Make a small change, commit and push.
  - Verify CI builds image, pushes it, and "deploys".
  - Expected: End-to-end pipeline works.

## Notes

- If you encounter issues, check the logs in GitHub Actions.
- For deployment, you may need a hosting service that supports Docker.
- Start with local Docker testing before CI/CD.
- Mark tasks as done by checking the boxes after completion.
- Use `.env.example.docker` as a template: copy it to `.env.docker` and update values as needed.
- For development, use `docker-compose up` with `Dockerfile.dev`.
- For production deployment, use `Dockerfile.prod` which provides separate PHP-FPM and Nginx images.