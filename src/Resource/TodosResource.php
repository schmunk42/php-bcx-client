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
