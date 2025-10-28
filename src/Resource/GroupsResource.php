<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

/**
 * Groups resource client
 *
 * Groups (also called Companies) represent organizations or companies in Basecamp.
 * They are used for organizing people and managing access control.
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/groups.md
 */
final class GroupsResource extends AbstractResource
{
    /**
     * Get all groups
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->client->get('/groups.json');
    }

    /**
     * Get a specific group
     *
     * @return array<string, mixed>
     */
    public function get(int $groupId): array
    {
        return $this->client->get(sprintf('/groups/%d.json', $groupId));
    }
}
