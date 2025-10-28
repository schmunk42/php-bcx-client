<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

/**
 * Documents resource client
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/documents.md
 */
final class DocumentsResource extends AbstractResource
{
    /**
     * Get all documents in a project
     *
     * @param array<string, mixed> $query Optional query parameters (sort: az, za, newest, oldest)
     * @return array<int, array<string, mixed>>
     */
    public function all(int $projectId, array $query = []): array
    {
        return $this->client->get(sprintf('/projects/%d/documents.json', $projectId), $query);
    }

    /**
     * Get all documents across all projects
     *
     * @param array<string, mixed> $query Optional query parameters (sort: az, za, newest, oldest)
     * @return array<int, array<string, mixed>>
     */
    public function allGlobal(array $query = []): array
    {
        return $this->client->get('/documents.json', $query);
    }

    /**
     * Get a specific document
     *
     * @return array<string, mixed>
     */
    public function get(int $projectId, int $documentId): array
    {
        return $this->client->get(sprintf('/projects/%d/documents/%d.json', $projectId, $documentId));
    }

    /**
     * Create a new document
     *
     * @param array<string, mixed> $data Should contain title and content
     * @return array<string, mixed>
     */
    public function create(int $projectId, array $data): array
    {
        return $this->client->post(sprintf('/projects/%d/documents.json', $projectId), $data);
    }

    /**
     * Update a document
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(int $projectId, int $documentId, array $data): array
    {
        return $this->client->put(
            sprintf('/projects/%d/documents/%d.json', $projectId, $documentId),
            $data
        );
    }

    /**
     * Delete a document
     */
    public function delete(int $projectId, int $documentId): void
    {
        $this->client->delete(sprintf('/projects/%d/documents/%d.json', $projectId, $documentId));
    }
}
