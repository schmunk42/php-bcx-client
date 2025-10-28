<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

/**
 * Messages resource client
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/messages.md
 */
final class MessagesResource extends AbstractResource
{
    /**
     * Get a specific message
     *
     * @return array<string, mixed>
     */
    public function get(int $projectId, int $messageId): array
    {
        return $this->client->get(sprintf('/projects/%d/messages/%d.json', $projectId, $messageId));
    }

    /**
     * Create a new message
     *
     * @param array<string, mixed> $data Should contain subject, content, and optionally subscribers
     * @return array<string, mixed>
     */
    public function create(int $projectId, array $data): array
    {
        return $this->client->post(sprintf('/projects/%d/messages.json', $projectId), $data);
    }

    /**
     * Update a message
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(int $projectId, int $messageId, array $data): array
    {
        return $this->client->put(
            sprintf('/projects/%d/messages/%d.json', $projectId, $messageId),
            $data
        );
    }

    /**
     * Delete a message
     */
    public function delete(int $projectId, int $messageId): void
    {
        $this->client->delete(sprintf('/projects/%d/messages/%d.json', $projectId, $messageId));
    }
}
