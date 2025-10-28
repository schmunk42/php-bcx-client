<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

/**
 * Topics resource client
 *
 * Topics provide a way to navigate and organize content in Basecamp.
 * They represent messages, todos, documents, etc. within projects.
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/topics.md
 */
final class TopicsResource extends AbstractResource
{
    /**
     * Get all topics in a specific project
     *
     * @param array<string, mixed> $query Optional query parameters (page: int)
     * @return array<int, array<string, mixed>>
     */
    public function allInProject(int $projectId, array $query = []): array
    {
        return $this->client->get(sprintf('/projects/%d/topics.json', $projectId), $query);
    }
}
