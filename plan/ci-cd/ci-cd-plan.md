# CI/CD Plan

## Overview

CI/CD will automate testing and deployment of our Laravel app.

## Recommended Workflow

Use GitHub Actions because:
- Free for public repos
- Easy to set up
- Integrates with GitHub

## Pipeline Stages

### 1. Install Dependencies
- Install PHP, Composer, Node.js
- Run `composer install` for PHP packages
- Run `npm install` for JS packages

### 2. Lint/Check Code
- Run `vendor/bin/pint --test` to check code formatting
- Run `vendor/bin/phpstan analyse` for static analysis
- Check for any syntax errors

### 3. Run Tests
- Run `php artisan test` to execute Pest tests
- Ensure all tests pass

### 4. Build Docker Image
- Build the Docker image using Dockerfile
- Tag with commit SHA or version

### 5. Push Docker Image
- Push image to Docker Hub or GitHub Container Registry

### 6. Deploy
- Deploy to staging/production server
- Pull latest image and restart services

## Workflow Triggers

- On push to main branch
- On pull requests

## Benefits

- Catch bugs early
- Automate deployment
- Ensure code quality