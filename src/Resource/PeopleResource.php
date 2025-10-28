<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

/**
 * People resource client
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/people.md
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/accesses.md
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
