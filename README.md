# Basecamp 2 (BCX) API Client for PHP

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

A modern, type-safe PHP 8.4 client for the Basecamp 2 API with OAuth 2.0 and HTTP Basic authentication support.

## Features

- ✅ PHP 8.4+ with strict typing
- ✅ Multiple authentication methods (OAuth 2.0 & HTTP Basic)
- ✅ All 12 Basecamp 2 resources implemented
- ✅ 100% test coverage (98 tests, 296 assertions)
- ✅ Symfony HttpClient integration
- ✅ PSR-3 logging support
- ✅ PHPStan level 8 compliant
- ✅ PSR-12 code style
- ✅ Docker development environment

## Installation

```bash
composer require schmunk42/php-bcx-client
```

## Quick Start

### Option 1: HTTP Basic Authentication (Simplest)

Perfect for development, debugging, and personal scripts:

```php
<?php

use Schmunk42\BasecampApi\Authentication\BasicAuthentication;
use Schmunk42\BasecampApi\Client\BasecampClient;

// Your Basecamp credentials
$accountId = '999999999'; // Your account ID
$username = 'you@example.com'; // Your Basecamp email
$password = 'your-password'; // Your Basecamp password

// Create authenticated client
$auth = new BasicAuthentication($username, $password);
$client = new BasecampClient($accountId, $auth);

// Get all projects
$projects = $client->projects()->all();

// Get current user
$me = $client->people()->me();
echo "Logged in as: {$me['name']}\n";
```

### Option 2: OAuth 2.0 (Recommended for Production)

For applications with multiple users:

```php
<?php

use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;

// OAuth 2.0 access token (obtained through OAuth flow)
$accessToken = 'BAhbByIBsHsidmVyc2lvbiI6MSwidXNlcl9pZCI...';
$accountId = '999999999';

// Create authenticated client
$auth = new OAuth2Authentication($accessToken);
$client = new BasecampClient($accountId, $auth);

// Use the API
$projects = $client->projects()->all();
```

**See [docs/OAUTH.md](docs/OAUTH.md)** for complete OAuth 2.0 setup guide.

## Requirements

- PHP 8.4 or higher
- Symfony HttpClient 7.0+

## Available Resources

All 12 Basecamp 2 resources are fully implemented:

| Resource | Description | Example |
|----------|-------------|---------|
| **Projects** | Manage projects | `$client->projects()->all()` |
| **Todolists** | Todo lists in projects | `$client->todolists()->all($projectId)` |
| **Todos** | Individual tasks | `$client->todos()->complete($projectId, $todoId)` |
| **People** | Users and access | `$client->people()->me()` |
| **Messages** | Message board posts | `$client->messages()->create($projectId, $data)` |
| **Comments** | Comments on resources | `$client->comments()->create($projectId, 'Todo', $todoId, $data)` |
| **Documents** | Project documents | `$client->documents()->all($projectId)` |
| **Uploads** | File attachments | `$client->uploads()->create($fileContent, $mimeType)` |
| **Events** | Activity feed | `$client->events()->all()` |
| **Calendar Events** | Scheduled events | `$client->calendarEvents()->all()` |
| **Topics** | Content navigation | `$client->topics()->allInProject($projectId)` |
| **Groups** | User groups/companies | `$client->groups()->all()` |

## Usage Examples

### Projects

```php
// List all projects
$projects = $client->projects()->all();

// Get archived projects
$archived = $client->projects()->archived();

// Create a new project
$project = $client->projects()->create([
    'name' => 'New Project',
    'description' => 'Project description',
]);

// Archive a project
$client->projects()->delete(123456);
```

### Todos

```php
// Get all todolists in a project
$todolists = $client->todolists()->all($projectId);

// Get todos in a todolist
$todos = $client->todos()->all($projectId, $todolistId);

// Create a new todo
$todo = $client->todos()->create($projectId, $todolistId, [
    'content' => 'Task description',
    'due_at' => '2025-12-31',
]);

// Mark todo as complete
$client->todos()->complete($projectId, $todoId);
```

### Messages & Comments

```php
// Create a message
$message = $client->messages()->create($projectId, [
    'subject' => 'Project Update',
    'content' => 'Here is the latest update...',
]);

// Add a comment to a message
$comment = $client->comments()->create(
    $projectId,
    'Message',
    $messageId,
    ['content' => 'Great update!']
);
```

### File Uploads

```php
// Upload a file (two-step process)
$fileContent = file_get_contents('/path/to/file.pdf');
$upload = $client->uploads()->create($fileContent, 'application/pdf');
$token = $upload['token'];

// Attach to a message
$message = $client->messages()->create($projectId, [
    'subject' => 'Document attached',
    'content' => 'See attached file',
    'attachments' => [
        ['token' => $token, 'name' => 'document.pdf']
    ],
]);
```

## Authentication

### Finding Your Account ID

**With Basic Auth:**
```php
$auth = new BasicAuthentication('you@example.com', 'password');
$client = new BasecampClient('999999999', $auth); // Try any number first
$me = $client->people()->me(); // Will show your accounts
```

**With OAuth 2.0:**
```bash
curl -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  https://launchpad.37signals.com/authorization.json
```

The `id` field in the response is your Account ID.

### OAuth 2.0 Setup

For production applications with multiple users, use OAuth 2.0:

1. **Register your app**: https://launchpad.37signals.com/integrations
2. **Get access token**: Follow the OAuth 2.0 flow
3. **Use with client**: See [docs/OAUTH.md](docs/OAUTH.md) for complete guide

## Development

### With Docker (Recommended)

```bash
# Clone repository
git clone https://github.com/schmunk42/php-bcx-client.git
cd php-bcx-client

# Setup environment
cp .env.example .env
# Edit .env with your credentials

# Install and test
make install
make test
make phpstan
make cs-check

# Run examples
make example         # Basic usage
make oauth-flow      # OAuth 2.0 flow
```

### Without Docker

```bash
composer install
composer test
composer phpstan
composer cs-check
```

## Testing

```bash
# Run all tests
make test
# or
composer test

# Run with coverage
docker compose run --rm php vendor/bin/phpunit --coverage-html coverage

# Static analysis
make phpstan

# Code style
make cs-fix
```

## Error Handling

```php
use Schmunk42\BasecampApi\Exception\AuthenticationException;
use Schmunk42\BasecampApi\Exception\RequestException;

try {
    $projects = $client->projects()->all();
} catch (AuthenticationException $e) {
    // Handle authentication errors (401)
    echo "Auth failed: " . $e->getMessage();
} catch (RequestException $e) {
    // Handle other API errors (400, 404, 500, etc.)
    echo "API error: " . $e->getStatusCode();
    echo "Response: " . $e->getResponseBody();
}
```

## Logging

Inject a PSR-3 logger for debugging:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('basecamp');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$client = new BasecampClient($accountId, $auth, null, $logger);

// All API requests will be logged
$projects = $client->projects()->all();
```

## Documentation

- **OAuth 2.0 Setup**: [docs/OAUTH.md](docs/OAUTH.md) - Complete guide with examples
- **Code Examples**: [examples/](examples/) - Working examples for all features
- **API Reference**: https://github.com/basecamp/bcx-api - Official Basecamp 2 API docs

## Contributing

Contributions are welcome! Please ensure:

- PHPUnit tests pass (`make test`)
- PHPStan level 8 passes (`make phpstan`)
- Code follows PSR-12 (`make cs-fix`)
- 100% test coverage for new code

## License

MIT License. See [LICENSE](LICENSE) file.

## Support

- **Issues**: https://github.com/schmunk42/php-bcx-client/issues
- **API Docs**: https://github.com/basecamp/bcx-api

## Credits

- Built with [Symfony HttpClient](https://symfony.com/doc/current/http_client.html)
- Inspired by the legacy [netvlies/basecamp-php](https://github.com/netvlies/basecamp-php) client
