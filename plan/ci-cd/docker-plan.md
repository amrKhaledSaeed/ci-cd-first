# Docker Plan

## Overview

Docker will help us run the Laravel application in containers. This makes it easy to set up the same environment everywhere.

## Required Files

### Dockerfile

This file tells Docker how to build the application image.

- Use PHP 8.4 image as base
- Install Composer and Node.js
- Copy composer.json and package.json first for better caching
- Install PHP and JS dependencies
- Copy application code
- Run build commands (composer install, npm run build)
- Expose port 8000 for Laravel's built-in server

### docker-compose.yml

This file defines the services needed to run the app locally.

- **app**: The Laravel application
- **db**: Database (MySQL)
- **web**: Web server (Nginx) to serve static files and proxy to app

### .dockerignore

This file tells Docker which files not to include in the image.

- .git
- node_modules
- vendor (after build)
- .env
- tests
- docs

## Build and Run Flow

1. Build the Docker image: `docker-compose build`
2. Start services: `docker-compose up`
3. Access app at http://localhost
4. For development, mount source code as volume

## Benefits

- Consistent environment
- Easy to share with team
- No "works on my machine" issues