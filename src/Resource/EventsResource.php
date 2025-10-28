<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

/**
 * Events resource client
 *
 * Events are activities that happen in Basecamp (todos created, messages posted, etc.)
 * Not to be confused with Calendar Events which are scheduled events.
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/events.md
 */
final class EventsResource extends AbstractResource
{
    /**
     * Get all events across all projects and calendars
     *
     * @param array<string, mixed> $query Optional query parameters (since: ISO 8601 datetime, page: int)
     * @return array<int, array<string, mixed>>
     */
    public function all(array $query = []): array
    {
        return $this->client->get('/events.json', $query);
    }

    /**
     * Get all events in a specific project
     *
     * @param array<string, mixed> $query Optional query parameters (since: ISO 8601 datetime, page: int)
     * @return array<int, array<string, mixed>>
     */
    public function allInProject(int $projectId, array $query = []): array
    {
        return $this->client->get(sprintf('/projects/%d/events.json', $projectId), $query);
    }

    /**
     * Get all events created by a specific person
     *
     * @param array<string, mixed> $query Optional query parameters (since: ISO 8601 datetime, page: int)
     * @return array<int, array<string, mixed>>
     */
    public function allByPerson(int $personId, array $query = []): array
    {
        return $this->client->get(sprintf('/people/%d/events.json', $personId), $query);
    }
}
