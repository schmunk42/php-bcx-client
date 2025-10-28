# Basecamp Classic (BCX) API Client for PHP

[![CI](https://github.com/schmunk42/php-bcx-api/actions/workflows/ci.yml/badge.svg)](https://github.com/schmunk42/php-bcx-api/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/schmunk42/php-bcx-api/branch/main/graph/badge.svg)](https://codecov.io/gh/schmunk42/php-bcx-api)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

A modern, type-safe PHP 8.4 client for the Basecamp Classic API with OAuth 2.0 support.

## Features

- PHP 8.4+ with strict typing
- OAuth 2.0 authentication
- Symfony HttpClient integration
- PSR-3 logging support
- Comprehensive test coverage with PHPUnit
- API Platform compatible
- Docker development environment included

## Installation

### For Library Users

```bash
composer require schmunk42/php-bcx-client
```

### For Development (with Docker)

```bash
# Clone the repository
git clone https://github.com/schmunk42/php-bcx-client.git
cd php-bcx-client

# Setup environment
cp .env.example .env
# Edit .env with your Basecamp credentials

# Install dependencies and run tests
make install
make test
```

## Requirements

- PHP 8.4 or higher
- Symfony HttpClient 7.0+

**OR**

- Docker & Docker Compose (for containerized development)

## Quick Start

```php
<?php

use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;

// Create authentication instance
$auth = new OAuth2Authentication('your-access-token');

// Initialize client with your account ID
$client = new BasecampClient('999999999', $auth);

// Get all projects
$projects = $client->projects()->all();

// Get specific project
$project = $client->projects()->get(123456);
```

## Authentication

This library uses OAuth 2.0 Bearer Token authentication for the Basecamp Classic API.

### Getting Your OAuth Access Token

#### Step 1: Register Your Application

1. Go to [Basecamp Launchpad](https://launchpad.37signals.com/integrations) (you need a Basecamp account)
2. Click "Register another application"
3. Fill in the application details:
   - **Name**: Your application name (e.g., "My Basecamp Integration")
   - **Company/Organization**: Your company name
   - **Website**: Your website URL
   - **Redirect URI**: Your OAuth callback URL (e.g., `https://yourapp.com/oauth/callback`)
     - For local development: `http://localhost:8080/callback`
4. Note down your **Client ID** and **Client Secret**

#### Step 2: Get Authorization from User

Direct users to the authorization URL:

```
https://launchpad.37signals.com/authorization/new?type=web_server&client_id=YOUR_CLIENT_ID&redirect_uri=YOUR_REDIRECT_URI
```

Replace:
- `YOUR_CLIENT_ID` - Your application's client ID
- `YOUR_REDIRECT_URI` - Your registered redirect URI (must match exactly)

#### Step 3: Exchange Authorization Code for Access Token

After user authorizes, Basecamp redirects to your `redirect_uri` with a `code` parameter:

```
https://yourapp.com/oauth/callback?code=AUTHORIZATION_CODE
```

Exchange this code for an access token:

```bash
curl -X POST https://launchpad.37signals.com/authorization/token \
  -d "type=web_server" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET" \
  -d "redirect_uri=YOUR_REDIRECT_URI" \
  -d "code=AUTHORIZATION_CODE"
```

Response:

```json
{
  "access_token": "BAhbByIBsHsidmVyc2lvbiI6MSwidXNlcl9pZCI...",
  "expires_in": 1209600,
  "refresh_token": "your-refresh-token"
}
```

#### Step 4: Find Your Account ID

Make a request to get your accounts:

```bash
curl -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  https://launchpad.37signals.com/authorization.json
```

Response includes your accounts with their IDs:

```json
{
  "accounts": [
    {
      "product": "bcx",
      "id": 999999999,
      "name": "Your Company",
      "href": "https://basecamp.com/999999999/api/v1"
    }
  ]
}
```

The `id` field (e.g., `999999999`) is your Account ID.

### Using the Access Token

```php
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use DateTimeImmutable;

// Simple token (no expiry tracking)
$auth = new OAuth2Authentication('BAhbByIBsHsidmVyc2lvbiI6MSwidXNlcl9pZCI...');

// Token with expiry (recommended)
$expiresAt = (new DateTimeImmutable())->modify('+14 days'); // 1209600 seconds
$auth = new OAuth2Authentication('your-access-token', $expiresAt);

// Use with client
$client = new BasecampClient('999999999', $auth);
```

### Token Refresh

Access tokens expire after 2 weeks (1,209,600 seconds). Use the refresh token to get a new access token:

```bash
curl -X POST https://launchpad.37signals.com/authorization/token \
  -d "type=refresh" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET" \
  -d "refresh_token=YOUR_REFRESH_TOKEN"
```

### Quick Testing (Development Only)

For quick testing, you can use Basecamp's personal access tokens:

1. Go to [Basecamp](https://basecamp.com)
2. Navigate to your account settings
3. Click "Apps & Integrations"
4. Create a personal access token

**Note**: Personal access tokens should only be used for development/testing, not production applications.

### Running OAuth Examples

#### With Docker (Recommended)

```bash
# On Host

# Set OAuth credentials
cp .env.example .env
# Edit .env with your Client ID and Client Secret

# Run OAuth flow example
docker compose run --rm php php examples/oauth-flow.php

# Or use make command
make shell
# In Container
php examples/oauth-flow.php
```

#### Native PHP

```bash
# On Host

export BASECAMP_CLIENT_ID="your-client-id"
export BASECAMP_CLIENT_SECRET="your-client-secret"
export BASECAMP_REDIRECT_URI="http://localhost:8080/callback"

php examples/oauth-flow.php
```

### See Also

- [Complete OAuth Setup Guide](./docs/OAUTH.md) - Step-by-step OAuth setup guide
- [OAuth Code Examples](./docs/OAUTH-EXAMPLES.md) - PHP code examples and patterns
- [Basecamp OAuth Documentation](https://github.com/basecamp/api/blob/master/sections/authentication.md)

## Usage Examples

### Projects

```php
// List all active projects
$projects = $client->projects()->all();

// List archived projects
$archived = $client->projects()->archived();

// Get specific project
$project = $client->projects()->get(123456);

// Create new project
$newProject = $client->projects()->create([
    'name' => 'My New Project',
    'description' => 'Project description',
]);

// Update project
$updated = $client->projects()->update(123456, [
    'name' => 'Updated Project Name',
]);

// Archive project
$client->projects()->delete(123456);

// Activate archived project
$client->projects()->activate(123456);
```

### Todolists & Todos

```php
// Get all todolists in a project
$todolists = $client->todolists()->all(123456);

// Get assigned todolists
$assigned = $client->todolists()->assigned();

// Create todolist
$todolist = $client->todolists()->create(123456, [
    'name' => 'My Todolist',
    'description' => 'Things to do',
]);

// Get todos in a todolist
$todos = $client->todos()->all(123456, 789012);

// Create todo
$todo = $client->todos()->create(123456, 789012, [
    'content' => 'Complete this task',
    'due_at' => '2025-12-31',
]);

// Complete a todo
$client->todos()->complete(123456, 345678);

// Uncomplete a todo
$client->todos()->uncomplete(123456, 345678);
```

### People & Access Management

```php
// Get all people
$people = $client->people()->all();

// Get current user
$me = $client->people()->me();

// Get specific person
$person = $client->people()->get(987654);

// Get people in a project
$projectPeople = $client->people()->inProject(123456);

// Grant project access
$client->people()->grantAccess(123456, [
    'person_id' => 987654,
]);

// Revoke project access
$client->people()->revokeAccess(123456, 987654);
```

## Logging

The client supports PSR-3 compatible loggers:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('basecamp');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$client = new BasecampClient('999999999', $auth, null, $logger);
```

## Custom HTTP Client

You can provide your own Symfony HttpClient instance:

```php
use Symfony\Component\HttpClient\HttpClient;

$httpClient = HttpClient::create([
    'timeout' => 30,
    'max_redirects' => 5,
]);

$client = new BasecampClient('999999999', $auth, $httpClient);
```

## Error Handling

The library throws specific exceptions for different error scenarios:

```php
use Schmunk42\BasecampApi\Exception\AuthenticationException;
use Schmunk42\BasecampApi\Exception\RequestException;

try {
    $projects = $client->projects()->all();
} catch (AuthenticationException $e) {
    // Handle authentication errors (401, expired token)
    echo "Authentication failed: " . $e->getMessage();
} catch (RequestException $e) {
    // Handle other API errors (400, 404, 500, etc.)
    echo "Request failed with status " . $e->getStatusCode();
    echo "Response: " . $e->getResponseBody();
}
```

## Development

### With Docker (Recommended)

```bash
# First time setup
make install            # Build image and install dependencies

# Run tests
make test

# Code quality
make cs-check           # Check code style
make cs-fix             # Fix code style
make phpstan            # Static analysis

# Examples
make example            # Run basic usage example
make oauth-flow         # Run OAuth flow (get access token)
make token-refresh      # Refresh expired token

# Other commands
make shell              # Open shell in container
make help               # Show all available commands
```

### Native PHP

```bash
# Install dependencies
composer install

# Run tests
composer test

# Code quality
composer cs-check       # Check code style
composer cs-fix         # Fix code style
composer phpstan        # Static analysis
```

## API Documentation

For complete API documentation, see:
- [Basecamp Classic API Documentation](https://github.com/basecamp/bcx-api)
- [Projects](https://github.com/basecamp/bcx-api/blob/master/sections/projects.md)
- [Todolists](https://github.com/basecamp/bcx-api/blob/master/sections/todolists.md)
- [Todos](https://github.com/basecamp/bcx-api/blob/master/sections/todos.md)
- [People](https://github.com/basecamp/bcx-api/blob/master/sections/people.md)

## License

MIT

## Credits

- Original PHP 5.x implementation: [netvlies/basecamp-php](https://github.com/netvlies/basecamp-php)
- Basecamp Classic API: [basecamp/bcx-api](https://github.com/basecamp/bcx-api)
