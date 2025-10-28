<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Exception\AuthenticationException;
use Schmunk42\BasecampApi\Exception\RequestException;

// Configuration
$accountId = getenv('BASECAMP_ACCOUNT_ID') ?: '999999999';
$accessToken = getenv('BASECAMP_ACCESS_TOKEN') ?: 'your-token-here';

// Create client
$auth = new OAuth2Authentication($accessToken);
$client = new BasecampClient($accountId, $auth);

try {
    // Get current user
    echo "Current User:\n";
    $me = $client->people()->me();
    print_r($me);
    echo "\n";

    // List all projects
    echo "Projects:\n";
    $projects = $client->projects()->all();
    foreach ($projects as $project) {
        echo sprintf("- [%d] %s\n", $project['id'], $project['name']);
    }
    echo "\n";

    // If you have projects, get todolists from the first one
    if (!empty($projects)) {
        $projectId = $projects[0]['id'];
        echo sprintf("Todolists in project %d:\n", $projectId);

        $todolists = $client->todolists()->all($projectId);
        foreach ($todolists as $todolist) {
            echo sprintf("- [%d] %s\n", $todolist['id'], $todolist['name']);
        }
    }

} catch (AuthenticationException $e) {
    echo "Authentication Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (RequestException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    echo "Status Code: " . $e->getStatusCode() . "\n";
    exit(1);
}
