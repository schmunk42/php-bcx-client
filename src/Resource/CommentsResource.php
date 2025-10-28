<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

/**
 * Comments resource client
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/comments.md
 */
final class CommentsResource extends AbstractResource
{
    /**
     * Create a comment on a resource (todo, message, etc.)
     *
     * @param string $section The section type (e.g., 'todos', 'messages')
     * @param array<string, mixed> $data Should contain content and optionally subscribers
     * @return array<string, mixed>
     */
    public function create(int $projectId, string $section, int $resourceId, array $data): array
    {
        return $this->client->post(
            sprintf('/projects/%d/%s/%d/comments.json', $projectId, $section, $resourceId),
            $data
        );
    }

    /**
     * Delete a comment
     */
    public function delete(int $projectId, int $commentId): void
    {
        $this->client->delete(sprintf('/projects/%d/comments/%d.json', $projectId, $commentId));
    }
}
