<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

/**
 * Todos resource client
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/todos.md
 */
final class TodosResource extends AbstractResource
{
    /**
     * Get all todos for a todolist
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(int $projectId, int $todolistId): array
    {
        return $this->client->get(
            sprintf('/projects/%d/todolists/%d/todos.json', $projectId, $todolistId)
        );
    }

    /**
     * Get all todos in a project across all todolists
     *
     * @param int $projectId The project ID
     * @param string|null $dueSince Optional ISO 8601 date (YYYY-MM-DD) to filter todos due after this date
     * @return array<int, array<string, mixed>> Array of todo objects
     */
    public function allInProject(int $projectId, ?string $dueSince = null): array
    {
        $url = sprintf('/projects/%d/todos.json', $projectId);
        $params = [];

        if ($dueSince !== null) {
            $params['due_since'] = $dueSince;
        }

        return $this->client->get($url, $params);
    }

    /**
     * Get completed todos in a todolist
     *
     * @param int $projectId The project ID
     * @param int $todolistId The todolist ID
     * @return array<int, array<string, mixed>> Array of completed todo objects
     */
    public function getCompleted(int $projectId, int $todolistId): array
    {
        return $this->client->get(
            sprintf('/projects/%d/todolists/%d/todos/completed.json', $projectId, $todolistId)
        );
    }

    /**
     * Get remaining (active) todos in a todolist
     *
     * @param int $projectId The project ID
     * @param int $todolistId The todolist ID
     * @return array<int, array<string, mixed>> Array of remaining todo objects
     */
    public function getRemaining(int $projectId, int $todolistId): array
    {
        return $this->client->get(
            sprintf('/projects/%d/todolists/%d/todos/remaining.json', $projectId, $todolistId)
        );
    }

    /**
     * Get trashed todos in a todolist
     *
     * @param int $projectId The project ID
     * @param int $todolistId The todolist ID
     * @return array<int, array<string, mixed>> Array of trashed todo objects
     */
    public function getTrashed(int $projectId, int $todolistId): array
    {
        return $this->client->get(
            sprintf('/projects/%d/todolists/%d/todos/trashed.json', $projectId, $todolistId)
        );
    }

    /**
     * Get all completed todos in a project across all todolists
     *
     * @param int $projectId The project ID
     * @return array<int, array<string, mixed>> Array of completed todo objects
     */
    public function getAllCompletedInProject(int $projectId): array
    {
        return $this->client->get(sprintf('/projects/%d/todos/completed.json', $projectId));
    }

    /**
     * Get all remaining (active) todos in a project across all todolists
     *
     * @param int $projectId The project ID
     * @return array<int, array<string, mixed>> Array of remaining todo objects
     */
    public function getAllRemainingInProject(int $projectId): array
    {
        return $this->client->get(sprintf('/projects/%d/todos/remaining.json', $projectId));
    }

    /**
     * Get a specific todo
     *
     * @return array<string, mixed>
     */
    public function get(int $projectId, int $todoId): array
    {
        return $this->client->get(sprintf('/projects/%d/todos/%d.json', $projectId, $todoId));
    }

    /**
     * Create a new todo
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(int $projectId, int $todolistId, array $data): array
    {
        return $this->client->post(
            sprintf('/projects/%d/todolists/%d/todos.json', $projectId, $todolistId),
            $data
        );
    }

    /**
     * Update a todo
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(int $projectId, int $todoId, array $data): array
    {
        return $this->client->put(
            sprintf('/projects/%d/todos/%d.json', $projectId, $todoId),
            $data
        );
    }

    /**
     * Delete a todo
     */
    public function delete(int $projectId, int $todoId): void
    {
        $this->client->delete(sprintf('/projects/%d/todos/%d.json', $projectId, $todoId));
    }

    /**
     * Mark a todo as complete
     *
     * @return array<string, mixed>
     */
    public function complete(int $projectId, int $todoId): array
    {
        return $this->client->put(
            sprintf('/projects/%d/todos/%d.json', $projectId, $todoId),
            ['completed' => true]
        );
    }

    /**
     * Mark a todo as incomplete
     *
     * @return array<string, mixed>
     */
    public function uncomplete(int $projectId, int $todoId): array
    {
        return $this->client->put(
            sprintf('/projects/%d/todos/%d.json', $projectId, $todoId),
            ['completed' => false]
        );
    }
}
