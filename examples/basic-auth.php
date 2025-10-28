<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Schmunk42\BasecampApi\Authentication\BasicAuthentication;
use Schmunk42\BasecampApi\Client\BasecampClient;

/**
 * Basic Authentication Example
 *
 * This example demonstrates using HTTP Basic Authentication with the Basecamp 2 API.
 * Basic auth is simpler than OAuth 2.0 and great for:
 * - Development and debugging
 * - Internal tools
 * - Personal scripts
 *
 * For production applications with multiple users, use OAuth 2.0 instead.
 */

// Load credentials from environment
$accountId = getenv('BASECAMP_ACCOUNT_ID');
$username = getenv('BASECAMP_USERNAME'); // Your Basecamp email
$password = getenv('BASECAMP_PASSWORD'); // Your Basecamp password

if (!$accountId || !$username || !$password) {
    echo "Error: Missing required environment variables\n\n";
    echo "Required:\n";
    echo "  BASECAMP_ACCOUNT_ID  - Your Basecamp account ID\n";
    echo "  BASECAMP_USERNAME    - Your Basecamp email address\n";
    echo "  BASECAMP_PASSWORD    - Your Basecamp password\n\n";
    echo "Example:\n";
    echo "  export BASECAMP_ACCOUNT_ID=\"999999999\"\n";
    echo "  export BASECAMP_USERNAME=\"you@example.com\"\n";
    echo "  export BASECAMP_PASSWORD=\"your-password\"\n";
    echo "  php examples/basic-auth.php\n";
    exit(1);
}

echo "Basecamp 2 API - Basic Authentication Example\n";
echo str_repeat('=', 50) . "\n\n";

try {
    // Create Basic Authentication
    $auth = new BasicAuthentication($username, $password);

    // Create Basecamp client
    $client = new BasecampClient($accountId, $auth);

    // Get current user
    echo "1. Current User\n";
    echo str_repeat('-', 50) . "\n";
    $me = $client->people()->me();
    echo sprintf("ID: %d\n", $me['id']);
    echo sprintf("Name: %s\n", $me['name']);
    echo sprintf("Email: %s\n\n", $me['email_address']);

    // Get all projects
    echo "2. Projects\n";
    echo str_repeat('-', 50) . "\n";
    $projects = $client->projects()->all();
    echo sprintf("Total projects: %d\n\n", count($projects));

    if (count($projects) > 0) {
        echo "Recent projects:\n";
        foreach (array_slice($projects, 0, 5) as $project) {
            echo sprintf("  [%d] %s\n", $project['id'], $project['name']);
        }

        if (count($projects) > 5) {
            echo sprintf("  ... and %d more\n", count($projects) - 5);
        }
    }

    echo "\n✅ Success! Basic authentication is working.\n\n";
    echo "Note: For production applications with multiple users,\n";
    echo "consider using OAuth 2.0 authentication instead.\n";
    echo "See: docs/OAUTH.md\n";

} catch (\Schmunk42\BasecampApi\Exception\AuthenticationException $e) {
    echo "❌ Authentication failed!\n\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Status: " . $e->getCode() . "\n\n";
    echo "Possible causes:\n";
    echo "  - Invalid username or password\n";
    echo "  - Account ID doesn't match your credentials\n";
    echo "  - Account doesn't have API access enabled\n";
    exit(1);
} catch (\Schmunk42\BasecampApi\Exception\RequestException $e) {
    echo "❌ API request failed!\n\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Status: " . $e->getStatusCode() . "\n";
    if ($e->getResponseBody()) {
        echo "Response: " . $e->getResponseBody() . "\n";
    }
    exit(1);
} catch (\Exception $e) {
    echo "❌ Unexpected error!\n\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
