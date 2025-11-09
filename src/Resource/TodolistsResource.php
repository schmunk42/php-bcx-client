<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

/**
 * Todolists resource client
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/todolists.md
 */
final class TodolistsResource extends AbstractResource
{
    /**
     * Get all active todolists across all projects
     *
     * @return array<int, array<string, mixed>>
     */
    public function allGlobal(): array
    {
        return $this->client->get('/todolists.json');
    }

    /**
     * Get all completed todolists across all projects
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCompletedGlobal(): array
    {
        return $this->client->get('/todolists/completed.json');
    }

    /**
     * Get all trashed todolists across all projects
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTrashedGlobal(): array
    {
        return $this->client->get('/todolists/trashed.json');
    }

    /**
     * Get all assigned todolists
     *
     * @return array<int, array<string, mixed>>
     */
    public function assigned(): array
    {
        return $this->client->get('/todolists/assigned.json');
    }

    /**
     * Get all todolists for a project
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(int $projectId): array
    {
        return $this->client->get(sprintf('/projects/%d/todolists.json', $projectId));
    }

    /**
     * Get all completed todolists for a project
     *
     * @return array<int, array<string, mixed>>
     */
    public function completed(int $projectId): array
    {
        return $this->client->get(sprintf('/projects/%d/todolists/completed.json', $projectId));
    }

    /**
     * Get all trashed todolists for a project
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTrashed(int $projectId): array
    {
        return $this->client->get(sprintf('/projects/%d/todolists/trashed.json', $projectId));
    }

    /**
     * Get a specific todolist
     *
     * @param int $projectId The project ID
     * @param int $todolistId The todolist ID
     * @param bool $excludeTodos Whether to exclude todos from the response (recommended for 1000+ items)
     * @return array<string, mixed>
     */
    public function get(int $projectId, int $todolistId, bool $excludeTodos = false): array
    {
        $url = sprintf('/projects/%d/todolists/%d.json', $projectId, $todolistId);
        $params = [];

        if ($excludeTodos) {
            $params['exclude_todos'] = 'true';
        }

        return $this->client->get($url, $params);
    }

    /**
     * Create a new todolist
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(int $projectId, array $data): array
    {
        return $this->client->post(sprintf('/projects/%d/todolists.json', $projectId), $data);
    }

    /**
     * Update a todolist
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(int $projectId, int $todolistId, array $data): array
    {
        return $this->client->put(
            sprintf('/projects/%d/todolists/%d.json', $projectId, $todolistId),
            $data
        );
    }

    /**
     * Delete a todolist
     */
    public function delete(int $projectId, int $todolistId): void
    {
        $this->client->delete(sprintf('/projects/%d/todolists/%d.json', $projectId, $todolistId));
    }
}
