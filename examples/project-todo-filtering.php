<?php
// file generated with AI assistance: Claude Code - 2025-11-09 00:00:00

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Exception\BasecampApiException;

/**
 * Example: Project-level Todo Filtering
 *
 * This example demonstrates how to:
 * - Get all todos across all todolists in a project
 * - Filter todos by status (completed, remaining, trashed)
 * - Filter todos by due date
 * - Get global todolist queries
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
    echo "=== Project Todo Filtering Example ===\n\n";

    // Get first active project
    echo "1. Getting first active project...\n";
    $projects = $client->projects()->all();

    if (empty($projects)) {
        echo "   No active projects found. Please create a project first.\n";
        exit(0);
    }

    $project = $projects[0];
    $projectId = $project['id'];
    echo "   Using project: {$project['name']} (ID: {$projectId})\n\n";

    // Get all todos across all todolists
    echo "2. Getting all todos in project...\n";
    $allTodos = $client->todos()->allInProject($projectId);
    echo "   Found " . count($allTodos) . " total todos\n\n";

    // Get completed todos
    echo "3. Getting completed todos in project...\n";
    $completedTodos = $client->todos()->getAllCompletedInProject($projectId);
    echo "   Found " . count($completedTodos) . " completed todos\n";

    if (!empty($completedTodos)) {
        echo "   Recent completed todos:\n";
        foreach (array_slice($completedTodos, 0, 5) as $todo) {
            echo "      {$todo['content']}\n";
        }
    }
    echo "\n";

    // Get remaining (active) todos
    echo "4. Getting remaining todos in project...\n";
    $remainingTodos = $client->todos()->getAllRemainingInProject($projectId);
    echo "   Found " . count($remainingTodos) . " remaining todos\n";

    if (!empty($remainingTodos)) {
        echo "   Active todos:\n";
        foreach (array_slice($remainingTodos, 0, 5) as $todo) {
            $dueDate = $todo['due_at'] ?? 'No due date';
            echo "     Ë {$todo['content']} (Due: {$dueDate})\n";
        }
    }
    echo "\n";

    // Filter todos by due date
    $dueSince = date('Y-m-d'); // Today
    echo "5. Getting todos due after {$dueSince}...\n";
    $upcomingTodos = $client->todos()->allInProject($projectId, $dueSince);
    echo "   Found " . count($upcomingTodos) . " upcoming todos\n\n";

    // Global todolist queries
    echo "6. Getting global todolists (across all projects)...\n";

    $activeLists = $client->todolists()->allGlobal();
    echo "   Active todolists: " . count($activeLists) . "\n";

    $completedLists = $client->todolists()->getCompletedGlobal();
    echo "   Completed todolists: " . count($completedLists) . "\n";

    $trashedLists = $client->todolists()->getTrashedGlobal();
    echo "   Trashed todolists: " . count($trashedLists) . "\n\n";

    // Get a todolist with excludeTodos option (useful for large lists)
    if (!empty($activeLists)) {
        $firstList = $activeLists[0];
        $listProjectId = $firstList['bucket']['id'] ?? $projectId;
        $listId = $firstList['id'];

        echo "7. Demonstrating excludeTodos option...\n";
        echo "   Getting todolist with todos:\n";
        $listWithTodos = $client->todolists()->get($listProjectId, $listId, false);
        $todosIncluded = isset($listWithTodos['todos']) ? 'Yes' : 'No';
        echo "     Todos included: {$todosIncluded}\n";

        echo "   Getting todolist without todos (recommended for 1000+ items):\n";
        $listWithoutTodos = $client->todolists()->get($listProjectId, $listId, true);
        $todosExcluded = !isset($listWithoutTodos['todos']) || $listWithoutTodos['todos'] === null ? 'Yes' : 'No';
        echo "     Todos excluded: {$todosExcluded}\n";
    }
    echo "\n";

    echo "=== Example completed successfully ===\n";

} catch (BasecampApiException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
