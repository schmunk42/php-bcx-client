<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

/**
 * Uploads (Attachments) resource client
 *
 * Note: Basecamp uses "attachments" in the API but we call this "uploads" to distinguish
 * from the attachment tokens used when creating content.
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/attachments.md
 */
final class UploadsResource extends AbstractResource
{
    /**
     * Get all attachments in a project
     *
     * @param array<string, mixed> $query Optional query parameters (page, sort: az, za, biggest, smallest, newest, oldest)
     * @return array<int, array<string, mixed>>
     */
    public function all(int $projectId, array $query = []): array
    {
        return $this->client->get(sprintf('/projects/%d/attachments.json', $projectId), $query);
    }

    /**
     * Get all attachments across all projects
     *
     * @param array<string, mixed> $query Optional query parameters (page, sort)
     * @return array<int, array<string, mixed>>
     */
    public function allGlobal(array $query = []): array
    {
        return $this->client->get('/attachments.json', $query);
    }

    /**
     * Get a specific attachment
     *
     * @return array<string, mixed>
     */
    public function get(int $projectId, int $attachmentId): array
    {
        return $this->client->get(sprintf('/projects/%d/attachments/%d.json', $projectId, $attachmentId));
    }

    /**
     * Upload a file and get an attachment token
     *
     * This token can then be used when creating messages, todos, comments, etc.
     *
     * @param string $fileContent The binary file content
     * @param string $contentType MIME type (e.g., 'image/jpeg', 'application/pdf')
     * @return array<string, mixed> Returns array with 'token' key
     */
    public function create(string $fileContent, string $contentType): array
    {
        return $this->client->post('/attachments.json', $fileContent, [
            'Content-Type' => $contentType,
            'Content-Length' => (string) strlen($fileContent),
        ]);
    }

    /**
     * Rename an attachment
     *
     * Note: Linked files (e.g., Google Docs) cannot be renamed
     *
     * @param array<string, mixed> $data Should contain 'name' key
     * @return array<string, mixed>
     */
    public function update(int $projectId, int $attachmentId, array $data): array
    {
        return $this->client->put(
            sprintf('/projects/%d/attachments/%d.json', $projectId, $attachmentId),
            $data
        );
    }

    /**
     * Delete an attachment
     */
    public function delete(int $projectId, int $attachmentId): void
    {
        $this->client->delete(sprintf('/projects/%d/attachments/%d.json', $projectId, $attachmentId));
    }
}
