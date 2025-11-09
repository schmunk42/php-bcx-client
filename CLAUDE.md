# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# Basecamp 2 API Client (php-bcx-client)

A modern PHP 8.4 client for the Basecamp 2 API (BCX) with OAuth 2.0 authentication, built using Symfony HttpClient and compatible with API Platform.

## Project Commands

### Docker (Recommended)

```bash
# On Host

# Setup (first time)
make install            # Build image and install dependencies

# Development
make test               # Run PHPUnit tests
make cs-check           # Check code style
make cs-fix             # Fix code style issues
make phpstan            # Run static analysis
make shell              # Open shell in container

# Examples
make example            # Run basic usage example
make oauth-flow         # Run OAuth flow (get access token)
make token-refresh      # Run token refresh example

# Docker management
make build              # Build Docker image
make up                 # Start containers
make down               # Stop containers
make clean              # Remove containers and volumes
```

### Native PHP (Alternative)

```bash
# On Host

# Install dependencies
composer install

# Run tests
composer test

# Code quality checks
composer cs-check       # Check code style
composer cs-fix         # Fix code style issues
composer phpstan        # Run static analysis

# Development
composer dump-autoload  # Regenerate autoloader
```

## Architecture Overview

### Directory Structure

```
src/
├── Authentication/     # Authentication strategies
│   ├── AuthenticationInterface.php
│   └── OAuth2Authentication.php
├── Client/            # Main HTTP client
│   └── BasecampClient.php
├── Resource/          # API resource clients
│   ├── AbstractResource.php
│   ├── ProjectsResource.php
│   ├── TodolistsResource.php
│   ├── TodosResource.php
│   └── PeopleResource.php
└── Exception/         # Custom exceptions
    ├── BasecampApiException.php
    ├── AuthenticationException.php
    └── RequestException.php
```

### Core Design Patterns

**Resource-based Architecture**
- Each Basecamp API resource (Projects, Todos, People) has a dedicated Resource class
- Resources are lazy-loaded through the main `BasecampClient`
- All resource classes extend `AbstractResource` for shared functionality

**Strategy Pattern for Authentication**
- `AuthenticationInterface` defines the contract
- `OAuth2Authentication` implements OAuth 2.0 bearer token auth
- Extensible for future auth methods (tokens, API keys)

**Dependency Injection**
- HttpClient can be injected for testing or customization
- PSR-3 Logger can be injected for debugging
- Follows constructor property promotion (PHP 8.x feature)

**Exception Hierarchy**
- `BasecampApiException` - Base exception for all API errors
- `AuthenticationException` - 401/authentication failures
- `RequestException` - Other HTTP errors with status code and response body

### Key Components

**BasecampClient** (`src/Client/BasecampClient.php`)
- Main entry point for API interactions
- Handles HTTP requests (GET, POST, PUT, DELETE)
- Manages authentication headers and error handling
- Provides lazy-loaded resource accessors

**Resource Classes** (`src/Resource/`)
- Encapsulate API endpoints for specific resources
- Provide type-safe methods matching API operations
- Handle URL construction and parameter passing
- Examples: `all()`, `get()`, `create()`, `update()`, `delete()`

**Authentication** (`src/Authentication/`)
- OAuth2Authentication stores access token and optional expiry
- `isValid()` checks token expiration
- `getHeaders()` returns Authorization header for requests
- See [docs/OAUTH.md](../docs/OAUTH.md) for complete OAuth implementation guide

## API Documentation Reference

Official Basecamp 2 (BCX) API: https://github.com/basecamp/bcx-api

### Implemented Resources

- **Projects**: List, get, create, update, archive/activate projects
- **Todolists**: Manage todolists within projects, global queries (active/completed/trashed), exclude todos for large lists
- **Todos**: CRUD operations, mark complete/incomplete, project-level queries, filter by status (completed/remaining/trashed)
- **People**: User management, project access control, assigned todos, activity events, project access list

## Docker Setup

This project includes a complete Docker development environment with PHP 8.4 CLI.

### Configuration

Environment variables are managed via `.env` file (ignored by git):

```bash
# On Host

# Copy example configuration
cp .env.example .env

# Edit with your credentials
# BASECAMP_ACCOUNT_ID=your-account-id
# BASECAMP_ACCESS_TOKEN=your-access-token
```

### Docker Components

**Dockerfile**
- Based on `php:8.4-cli-alpine` for minimal footprint
- Includes Composer 2
- PHP extensions: zip, opcache with JIT enabled
- Optimized for CLI performance

**docker-compose.yml**
- Single PHP service for CLI operations
- Mounts project directory and vendor as volumes
- Loads environment variables from `.env`
- Interactive TTY for shell access

**Makefile**
- Color-coded help system (`make help`)
- Common development tasks wrapped in simple commands
- Follows DRY principle for Docker operations

## Development Notes

### PHP 8.4 Features Used

- Readonly properties for immutable objects
- Constructor property promotion
- Typed properties and return types
- Array shapes in docblocks for better IDE support

### Testing Strategy

- Unit tests with PHPUnit 11.0
- MockHttpClient for HTTP response simulation
- No real API calls in tests (fully mocked)
- Test coverage for authentication, client, and resources

### Code Quality Standards

- Strict types declaration in all files
- PSR-12 coding style enforced via PHP-CS-Fixer
- PHPStan level 8 static analysis
- Comprehensive PHPDoc blocks with type information

### Extension Points

To add new Basecamp API resources:
1. Create new Resource class in `src/Resource/`
2. Extend `AbstractResource`
3. Add accessor method in `BasecampClient`
4. Add corresponding unit tests
5. Update README with usage examples

### Common Development Tasks

**Adding a new API endpoint:**
```php
// In appropriate Resource class
public function methodName(int $id, array $data = []): array
{
    return $this->client->get(sprintf('/endpoint/%d.json', $id), $data);
}
```

**Creating tests:**
- Use MockHttpClient for simulating responses
- Test both success and error scenarios
- Verify correct HTTP methods and URLs
- Check exception handling

## OAuth 2.0 Authentication

The library uses OAuth 2.0 for secure authentication. Key points:

**Getting Access Tokens:**
1. Register application at https://launchpad.37signals.com/integrations
2. Get Client ID and Client Secret
3. Implement OAuth flow to obtain access token
4. Retrieve Account ID from authorization response
5. Use access token with `OAuth2Authentication` class

**Quick Reference:**
- Access tokens expire after 2 weeks (1,209,600 seconds)
- Use refresh tokens to obtain new access tokens
- Personal access tokens available for testing (not for production)

**Documentation:**
- [docs/OAUTH.md](../docs/OAUTH.md) - Complete step-by-step setup guide
- [docs/OAUTH-EXAMPLES.md](../docs/OAUTH-EXAMPLES.md) - Code examples and patterns

## References

- [OAuth Setup Guide](../docs/OAUTH.md) - Step-by-step OAuth setup
- [OAuth Code Examples](../docs/OAUTH-EXAMPLES.md) - PHP implementation examples
- [Basecamp OAuth Documentation](https://github.com/basecamp/api/blob/master/sections/authentication.md)
- Legacy PHP 5.x implementation: https://github.com/netvlies/basecamp-php
- Symfony HttpClient: https://symfony.com/doc/current/http_client.html
- PSR-3 Logger Interface: https://www.php-fig.org/psr/psr-3/
