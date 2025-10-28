# Docker Setup Guide

This document provides detailed information about the Docker development environment.

## Overview

The project includes a complete Docker setup for PHP 8.4 CLI development, eliminating the need for local PHP installation.

## Components

### Dockerfile

Based on `php:8.4-cli-alpine` for minimal image size:

- **Base Image**: Official PHP 8.4 CLI Alpine Linux
- **Composer**: Version 2 (latest)
- **PHP Extensions**:
  - `zip` - For handling compressed archives
  - `opcache` - With JIT compilation enabled for performance
- **Configuration**:
  - Memory limit: 256M
  - OPcache JIT: Enabled with tracing mode
  - JIT buffer: 64M

### docker-compose.yml

Single service configuration:

- **Service Name**: `php`
- **Volumes**:
  - `.:/app` - Project root mounted for live development
  - `./vendor:/app/vendor` - Vendor directory for dependency caching
- **Environment Variables**: Loaded from `.env` file
- **Interactive**: TTY enabled for shell access

### Makefile

Provides convenient shortcuts for Docker operations:

```bash
make help           # Show all available commands with descriptions
make install        # Build image and install dependencies (first time)
make test           # Run PHPUnit tests
make cs-fix         # Fix code style issues
make cs-check       # Check code style
make phpstan        # Run static analysis
make shell          # Open interactive shell
make example        # Run basic usage example
make oauth-flow     # Run OAuth flow (get access token)
make token-refresh  # Refresh expired token
make clean          # Remove containers and volumes
```

## Quick Start

### Initial Setup

```bash
# On Host

# 1. Copy environment configuration
cp .env.example .env

# 2. Edit configuration (optional for testing)
nano .env

# 3. Build and setup
make install
```

### Daily Development

```bash
# On Host

# Run tests
make test

# Open shell for interactive work
make shell

# In Container
composer dump-autoload
php examples/basic-usage.php
exit

# Check code quality
make cs-check
make phpstan
```

## Environment Configuration

Create a `.env` file from the example:

```bash
# On Host
cp .env.example .env
```

Edit `.env` with your credentials:

```env
# Required for API usage
BASECAMP_ACCOUNT_ID=999999999
BASECAMP_ACCESS_TOKEN=your-oauth-token-here

# Optional: For OAuth token management
BASECAMP_CLIENT_ID=your-client-id
BASECAMP_CLIENT_SECRET=your-client-secret
BASECAMP_REFRESH_TOKEN=your-refresh-token
```

These variables are automatically loaded into the container.

## Volume Mounts

### Project Directory (`./:/app`)

- Allows live code editing on host
- Changes immediately reflected in container
- No need to rebuild for code changes

### Vendor Directory (`./vendor:/app/vendor`)

- Persists Composer dependencies
- Improves performance by avoiding repeated installations
- Can be cleaned with `make clean`

## Common Tasks

### Running Composer Commands

```bash
# On Host
docker compose run --rm php composer install
docker compose run --rm php composer require new/package

# Or use the shell
make shell

# In Container
composer install
composer require new/package
```

### Running PHP Scripts

```bash
# On Host
docker compose run --rm php php your-script.php

# Or
make shell

# In Container
php your-script.php
```

### Running OAuth Examples

#### Get Access Token (OAuth Flow)

```bash
# On Host

# 1. Set OAuth credentials in .env
cp .env.example .env
nano .env  # Add BASECAMP_CLIENT_ID and BASECAMP_CLIENT_SECRET

# 2. Run OAuth flow
make oauth-flow

# Or directly
docker compose run --rm php php examples/oauth-flow.php

# Or in shell
make shell
# In Container
php examples/oauth-flow.php
```

The script will:
1. Display the authorization URL
2. Prompt for authorization code
3. Exchange code for access token
4. Get account information
5. Test API with new token
6. Output credentials for .env file

#### Refresh Expired Token

```bash
# On Host

# Ensure .env has refresh token
make token-refresh

# Or directly
docker compose run --rm php php examples/token-refresh.php
```

### Debugging

```bash
# On Host
make shell

# In Container
php -v                          # Check PHP version
php -m                          # List loaded modules
php -i | grep -i opcache        # Check OPcache configuration
composer diagnose               # Check Composer issues
```

## Performance Optimization

### OPcache with JIT

The container is configured with OPcache JIT for improved performance:

```ini
opcache.enable_cli = 1
opcache.jit = tracing
opcache.jit_buffer_size = 64M
```

This provides significant performance improvements for CLI scripts.

### Volume Caching

The vendor directory is mounted as a volume to:
- Avoid repeated `composer install` runs
- Speed up container startup
- Persist dependencies between rebuilds

## Troubleshooting

### Permission Issues

If you encounter permission issues:

```bash
# On Host
docker compose run --rm -u root php chown -R www-data:www-data /app
```

### Vendor Directory Issues

If Composer dependencies are corrupted:

```bash
# On Host
make clean
make install
```

### Image Rebuild

After changing Dockerfile:

```bash
# On Host
make build
```

### Container Not Starting

Check logs:

```bash
# On Host
docker compose logs php
```

## Best Practices

1. **Don't mount entire project as volume in production** - Use COPY in Dockerfile
2. **Use .env for credentials** - Never commit `.env` to git
3. **Rebuild after Dockerfile changes** - `make build`
4. **Clean vendor occasionally** - `make clean && make install`
5. **Use make commands** - Simplifies Docker operations

## Advanced Usage

### Custom PHP Configuration

Create `docker/php.ini`:

```ini
memory_limit = 512M
max_execution_time = 300
```

Update Dockerfile:

```dockerfile
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
```

### Running Multiple Commands

```bash
# On Host
docker compose run --rm php sh -c "composer install && composer test"
```

### Accessing Container Logs

```bash
# On Host
docker compose logs -f php
```

## CI/CD Integration

Example GitHub Actions workflow:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Build Docker image
        run: docker compose build

      - name: Install dependencies
        run: docker compose run --rm php composer install

      - name: Run tests
        run: docker compose run --rm php composer test

      - name: Code style
        run: docker compose run --rm php composer cs-check

      - name: Static analysis
        run: docker compose run --rm php composer phpstan
```

## Resources

- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [PHP Official Docker Images](https://hub.docker.com/_/php)
- [Composer Docker Best Practices](https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md)
