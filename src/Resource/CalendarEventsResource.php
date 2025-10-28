<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

/**
 * Calendar Events resource client
 *
 * Calendar Events are scheduled events on calendars (meetings, deadlines, etc.)
 * Not to be confused with Events which are activity feed items.
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/calendar_events.md
 */
final class CalendarEventsResource extends AbstractResource
{
    /**
     * Get all calendar events across all calendars
     *
     * @param array<string, mixed> $query Optional query parameters
     * @return array<int, array<string, mixed>>
     */
    public function all(array $query = []): array
    {
        return $this->client->get('/calendar_events.json', $query);
    }

    /**
     * Get all calendar events in a specific calendar
     *
     * @param array<string, mixed> $query Optional query parameters
     * @return array<int, array<string, mixed>>
     */
    public function allInCalendar(int $calendarId, array $query = []): array
    {
        return $this->client->get(sprintf('/calendars/%d/calendar_events.json', $calendarId), $query);
    }

    /**
     * Get all calendar events in a specific project
     *
     * @param array<string, mixed> $query Optional query parameters
     * @return array<int, array<string, mixed>>
     */
    public function allInProject(int $projectId, array $query = []): array
    {
        return $this->client->get(sprintf('/projects/%d/calendar_events.json', $projectId), $query);
    }

    /**
     * Get past calendar events in a specific calendar
     *
     * @return array<int, array<string, mixed>>
     */
    public function past(int $calendarId): array
    {
        return $this->client->get(sprintf('/calendars/%d/calendar_events/past.json', $calendarId));
    }

    /**
     * Get a specific calendar event
     *
     * @return array<string, mixed>
     */
    public function get(int $calendarId, int $eventId): array
    {
        return $this->client->get(sprintf('/calendars/%d/calendar_events/%d.json', $calendarId, $eventId));
    }

    /**
     * Create a new calendar event
     *
     * @param array<string, mixed> $data Event data (summary, description, starts_at, ends_at, all_day, etc.)
     * @return array<string, mixed>
     */
    public function create(int $calendarId, array $data): array
    {
        return $this->client->post(sprintf('/calendars/%d/calendar_events.json', $calendarId), $data);
    }

    /**
     * Update an existing calendar event
     *
     * @param array<string, mixed> $data Event data to update
     * @return array<string, mixed>
     */
    public function update(int $calendarId, int $eventId, array $data): array
    {
        return $this->client->put(sprintf('/calendars/%d/calendar_events/%d.json', $calendarId, $eventId), $data);
    }

    /**
     * Delete a calendar event
     */
    public function delete(int $calendarId, int $eventId): void
    {
        $this->client->delete(sprintf('/calendars/%d/calendar_events/%d.json', $calendarId, $eventId));
    }
}
