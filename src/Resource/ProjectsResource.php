<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

/**
 * Projects resource client
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/projects.md
 */
final class ProjectsResource extends AbstractResource
{
    /**
     * Get all active projects
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->client->get('/projects.json');
    }

    /**
     * Get all archived projects
     *
     * @return array<int, array<string, mixed>>
     */
    public function archived(): array
    {
        return $this->client->get('/projects/archived.json');
    }

    /**
     * Get a specific project
     *
     * @return array<string, mixed>
     */
    public function get(int $projectId): array
    {
        return $this->client->get(sprintf('/projects/%d.json', $projectId));
    }

    /**
     * Create a new project
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->client->post('/projects.json', $data);
    }

    /**
     * Update a project
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(int $projectId, array $data): array
    {
        return $this->client->put(sprintf('/projects/%d.json', $projectId), $data);
    }

    /**
     * Delete a project (archive)
     */
    public function delete(int $projectId): void
    {
        $this->client->delete(sprintf('/projects/%d.json', $projectId));
    }

    /**
     * Activate an archived project
     *
     * @return array<string, mixed>
     */
    public function activate(int $projectId): array
    {
        return $this->client->put(sprintf('/projects/%d.json', $projectId), [
            'archived' => false,
        ]);
    }
}
