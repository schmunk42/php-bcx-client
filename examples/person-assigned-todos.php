<?php
// file generated with AI assistance: Claude Code - 2025-11-09 00:00:00

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Exception\BasecampApiException;

/**
 * Example: Get Assigned Todos for a Person
 *
 * This example demonstrates how to:
 * - Get all todos assigned to a specific person across all projects
 * - Filter assigned todos by due date
 * - Get person's activity events
 * - Get all projects accessible to a person
 */

// Load credentials from environment
$accountId = getenv('BASECAMP_ACCOUNT_ID');
$accessToken = getenv('BASECAMP_ACCESS_TOKEN');

if (!$accountId || !$accessToken) {
    echo "Error: BASECAMP_ACCOUNT_ID and BASECAMP_ACCESS_TOKEN environment variables must be set.\n";
    echo "Example: export BASECAMP_ACCOUNT_ID=999999999\n";
    echo "         export BASECAMP_ACCESS_TOKEN=your-token-here\n";
    exit(1);
}

// Create client
$auth = new OAuth2Authentication($accessToken);
$client = new BasecampClient($accountId, $auth);

try {
    echo "=== Person Assigned Todos Example ===\n\n";

    // Get current user
    echo "1. Getting current user...\n";
    $me = $client->people()->me();
    $personId = $me['id'];
    echo "   Logged in as: {$me['name']} (ID: {$personId})\n\n";

    // Get all assigned todos for the current user
    echo "2. Getting all assigned todos...\n";
    $assignedTodolists = $client->people()->getAssignedTodos($personId);
    echo "   Found " . count($assignedTodolists) . " todolists with assigned tasks\n\n";

    foreach ($assignedTodolists as $todolist) {
        echo "   Todolist: {$todolist['name']}\n";
        if (isset($todolist['assigned_todos']) && is_array($todolist['assigned_todos'])) {
            foreach ($todolist['assigned_todos'] as $todo) {
                $status = $todo['completed'] ? '' : 'Ë';
                $dueDate = $todo['due_at'] ?? 'No due date';
                echo "     {$status} {$todo['content']} (Due: {$dueDate})\n";
            }
        }
        echo "\n";
    }

    // Get assigned todos due after a specific date
    $dueSince = date('Y-m-d'); // Today
    echo "3. Getting todos due after {$dueSince}...\n";
    $upcomingTodos = $client->people()->getAssignedTodos($personId, $dueSince);
    echo "   Found " . count($upcomingTodos) . " todolists with upcoming tasks\n\n";

    // Get person's activity events
    echo "4. Getting recent activity events...\n";
    $events = $client->people()->getEvents($personId);
    echo "   Found " . count($events) . " recent events\n";

    // Show last 5 events
    $recentEvents = array_slice($events, 0, 5);
    foreach ($recentEvents as $event) {
        $action = $event['action'] ?? 'unknown';
        $target = $event['target'] ?? 'unknown';
        $createdAt = $event['created_at'] ?? 'unknown time';
        echo "   - {$action} {$target} at {$createdAt}\n";
    }
    echo "\n";

    // Get all projects accessible to the person
    echo "5. Getting accessible projects...\n";
    $projects = $client->people()->getProjects($personId);
    echo "   Found " . count($projects) . " accessible projects\n";

    foreach ($projects as $project) {
        $name = $project['name'] ?? 'Unnamed';
        $projectId = $project['id'] ?? 'unknown';
        echo "   - {$name} (ID: {$projectId})\n";
    }
    echo "\n";

    echo "=== Example completed successfully ===\n";

} catch (BasecampApiException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
