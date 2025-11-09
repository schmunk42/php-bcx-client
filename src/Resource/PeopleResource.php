<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

/**
 * People resource client
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/people.md
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/accesses.md
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/todolists.md#get-assigned-todos
 */
final class PeopleResource extends AbstractResource
{
    /**
     * Get all people visible to current user
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->client->get('/people.json');
    }

    /**
     * Get trashed (deleted) people - requires admin privileges
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTrashed(): array
    {
        return $this->client->get('/people/trashed.json');
    }

    /**
     * Get all people in a specific project
     *
     * @return array<int, array<string, mixed>>
     */
    public function inProject(int $projectId): array
    {
        return $this->client->get(sprintf('/projects/%d/accesses.json', $projectId));
    }

    /**
     * Get a specific person
     *
     * @return array<string, mixed>
     */
    public function get(int $personId): array
    {
        return $this->client->get(sprintf('/people/%d.json', $personId));
    }

    /**
     * Get current user
     *
     * @return array<string, mixed>
     */
    public function me(): array
    {
        return $this->client->get('/people/me.json');
    }

    /**
     * Get assigned todos for a specific person
     *
     * Returns all todolists containing items assigned to the specified person.
     * Each todolist includes an 'assigned_todos' array with the individual todo items.
     *
     * @param int $personId The person's ID
     * @param string|null $dueSince Optional ISO 8601 date (YYYY-MM-DD) to filter todos due after this date
     * @return array<int, array<string, mixed>> Array of todolist objects with assigned todos
     * @see https://github.com/basecamp/bcx-api/blob/master/sections/todolists.md#get-assigned-todos
     */
    public function getAssignedTodos(int $personId, ?string $dueSince = null): array
    {
        $url = sprintf('/people/%d/assigned_todos.json', $personId);
        $params = [];

        if ($dueSince !== null) {
            $params['due_since'] = $dueSince;
        }

        return $this->client->get($url, $params);
    }

    /**
     * Get events for a specific person
     *
     * Returns the activity stream for a specific person across all projects.
     *
     * @param int $personId The person's ID
     * @return array<int, array<string, mixed>> Array of event objects
     */
    public function getEvents(int $personId): array
    {
        return $this->client->get(sprintf('/people/%d/events.json', $personId));
    }

    /**
     * Get all projects accessible to a specific person
     *
     * @param int $personId The person's ID
     * @return array<int, array<string, mixed>> Array of project objects
     */
    public function getProjects(int $personId): array
    {
        return $this->client->get(sprintf('/people/%d/projects.json', $personId));
    }

    /**
     * Grant access to a project for a person
     *
     * @param array<string, mixed> $data Should contain person_id and optionally email_address
     * @return array<string, mixed>
     */
    public function grantAccess(int $projectId, array $data): array
    {
        return $this->client->post(sprintf('/projects/%d/accesses.json', $projectId), $data);
    }

    /**
     * Revoke access to a project for a person
     */
    public function revokeAccess(int $projectId, int $personId): void
    {
        $this->client->delete(sprintf('/projects/%d/accesses/%d.json', $projectId, $personId));
    }

    /**
     * Update access for a person in a project
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function updateAccess(int $projectId, int $personId, array $data): array
    {
        return $this->client->put(
            sprintf('/projects/%d/accesses/%d.json', $projectId, $personId),
            $data
        );
    }
}
