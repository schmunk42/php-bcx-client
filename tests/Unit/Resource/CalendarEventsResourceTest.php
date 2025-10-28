<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Resource;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Resource\CalendarEventsResource;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class CalendarEventsResourceTest extends TestCase
{
    private CalendarEventsResource $resource;
    private BasecampClient $client;

    protected function setUp(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'created_at' => '2015-08-11T07:35:43.000Z',
                    'updated_at' => '2015-09-16T10:44:15.000Z',
                    'summary' => 'Mock Event',
                    'description' => 'Testing',
                    'private' => false,
                    'trashed' => false,
                    'all_day' => true,
                    'starts_at' => '2015-09-17',
                    'ends_at' => '2015-09-23',
                    'remind_at' => '2015-09-17T06:00:00.000Z',
                    'url' => 'https://basecamp.com/99999999/api/v1/calendars/1/calendar_events/1.json',
                    'app_url' => 'https://basecamp.com/99999999/calendars/1/calendar_events/1',
                    'comments_count' => 0,
                ],
            ])),
        ]);

        $this->client = new BasecampClient('999999999', $auth, $httpClient);
        $this->resource = new CalendarEventsResource($this->client);
    }

    public function testAll(): void
    {
        $events = $this->resource->all();

        $this->assertIsArray($events);
        $this->assertCount(1, $events);
        $this->assertSame(1, $events[0]['id']);
        $this->assertSame('Mock Event', $events[0]['summary']);
        $this->assertTrue($events[0]['all_day']);
        $this->assertSame('2015-09-17', $events[0]['starts_at']);
    }

    public function testAllInCalendar(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'summary' => 'Team Meeting',
                    'description' => 'Weekly sync',
                    'all_day' => false,
                    'starts_at' => '2015-09-20T14:00:00.000Z',
                    'ends_at' => '2015-09-20T15:00:00.000Z',
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new CalendarEventsResource($client);

        $events = $resource->allInCalendar(123);

        $this->assertIsArray($events);
        $this->assertCount(1, $events);
        $this->assertSame('Team Meeting', $events[0]['summary']);
        $this->assertFalse($events[0]['all_day']);
    }

    public function testAllInProject(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 2,
                    'summary' => 'Project Deadline',
                    'all_day' => true,
                    'starts_at' => '2015-10-01',
                    'ends_at' => '2015-10-01',
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new CalendarEventsResource($client);

        $events = $resource->allInProject(456);

        $this->assertIsArray($events);
        $this->assertCount(1, $events);
        $this->assertSame('Project Deadline', $events[0]['summary']);
    }

    public function testPast(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 3,
                    'summary' => 'Past Event',
                    'starts_at' => '2015-08-01',
                    'ends_at' => '2015-08-01',
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new CalendarEventsResource($client);

        $events = $resource->past(123);

        $this->assertIsArray($events);
        $this->assertCount(1, $events);
        $this->assertSame('Past Event', $events[0]['summary']);
    }

    public function testGet(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 1,
                'created_at' => '2015-08-11T07:35:43.000Z',
                'updated_at' => '2015-09-16T10:44:15.000Z',
                'summary' => 'Mock Event',
                'description' => 'Testing',
                'private' => false,
                'trashed' => false,
                'all_day' => true,
                'starts_at' => '2015-09-17',
                'ends_at' => '2015-09-23',
                'remind_at' => '2015-09-17T06:00:00.000Z',
                'url' => 'https://basecamp.com/99999999/api/v1/calendars/1/calendar_events/1.json',
                'app_url' => 'https://basecamp.com/99999999/calendars/1/calendar_events/1',
                'comments_count' => 0,
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new CalendarEventsResource($client);

        $event = $resource->get(123, 1);

        $this->assertIsArray($event);
        $this->assertSame(1, $event['id']);
        $this->assertSame('Mock Event', $event['summary']);
        $this->assertSame('Testing', $event['description']);
        $this->assertArrayHasKey('remind_at', $event);
        $this->assertArrayHasKey('comments_count', $event);
    }

    public function testCreate(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 1,
                'created_at' => '2015-08-11T07:35:43.000Z',
                'updated_at' => '2015-09-16T10:44:15.000Z',
                'summary' => 'New Event',
                'description' => 'Test Description',
                'private' => false,
                'trashed' => false,
                'all_day' => true,
                'starts_at' => '2015-09-17',
                'ends_at' => '2015-09-23',
                'url' => 'https://basecamp.com/99999999/api/v1/calendars/1/calendar_events/1.json',
                'app_url' => 'https://basecamp.com/99999999/calendars/1/calendar_events/1',
                'comments_count' => 0,
            ]), ['http_code' => 201]),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new CalendarEventsResource($client);

        $event = $resource->create(123, [
            'summary' => 'New Event',
            'description' => 'Test Description',
            'all_day' => true,
            'starts_at' => '2015-09-17',
            'ends_at' => '2015-09-23',
        ]);

        $this->assertIsArray($event);
        $this->assertSame(1, $event['id']);
        $this->assertSame('New Event', $event['summary']);
        $this->assertSame('Test Description', $event['description']);
    }

    public function testUpdate(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 1,
                'summary' => 'Updated Event',
                'description' => 'Updated Description',
                'all_day' => false,
                'starts_at' => '2015-09-20T14:00:00.000Z',
                'ends_at' => '2015-09-20T15:00:00.000Z',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new CalendarEventsResource($client);

        $event = $resource->update(123, 1, [
            'summary' => 'Updated Event',
            'description' => 'Updated Description',
        ]);

        $this->assertIsArray($event);
        $this->assertSame('Updated Event', $event['summary']);
        $this->assertSame('Updated Description', $event['description']);
    }

    public function testDelete(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 204]),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new CalendarEventsResource($client);

        $resource->delete(123, 1);

        // If we get here without exception, the test passes
        $this->assertTrue(true);
    }
}
