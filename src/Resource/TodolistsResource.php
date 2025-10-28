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
     * Get all assigned todolists
     *
     * @return array<int, array<string, mixed>>
     */
    public function assigned(): array
    {
        return $this->client->get('/todolists/assigned.json');
    }

    /**
     * Get a specific todolist
     *
     * @return array<string, mixed>
     */
    public function get(int $projectId, int $todolistId): array
    {
        return $this->client->get(sprintf('/projects/%d/todolists/%d.json', $projectId, $todolistId));
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
