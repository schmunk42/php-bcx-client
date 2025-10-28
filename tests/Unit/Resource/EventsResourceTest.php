<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Resource;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Resource\EventsResource;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class EventsResourceTest extends TestCase
{
    private EventsResource $resource;
    private BasecampClient $client;

    protected function setUp(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 99999999,
                    'created_at' => '2013-05-14T13:35:45.000+02:00',
                    'summary' => 'gave test access to a project',
                ],
            ])),
        ]);

        $this->client = new BasecampClient('999999999', $auth, $httpClient);
        $this->resource = new EventsResource($this->client);
    }

    public function testAll(): void
    {
        $events = $this->resource->all();

        $this->assertIsArray($events);
        $this->assertCount(1, $events);
        $this->assertSame(99999999, $events[0]['id']);
        $this->assertSame('gave test access to a project', $events[0]['summary']);
    }

    public function testAllWithSinceParameter(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                ['id' => 1, 'summary' => 'Event 1'],
                ['id' => 2, 'summary' => 'Event 2'],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new EventsResource($client);

        $events = $resource->all(['since' => '2013-05-09T16:16:59+02:00']);

        $this->assertIsArray($events);
        $this->assertCount(2, $events);
    }

    public function testAllWithPagination(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                ['id' => 51, 'summary' => 'Event 51'],
                ['id' => 52, 'summary' => 'Event 52'],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new EventsResource($client);

        $events = $resource->all(['page' => 2]);

        $this->assertIsArray($events);
        $this->assertCount(2, $events);
    }

    public function testAllInProject(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1054456336,
                    'created_at' => '2012-03-24T11:00:50-05:00',
                    'updated_at' => '2012-03-24T11:00:50-05:00',
                    'summary' => 're-assigned a to-do to Funky ones: Design it',
                    'url' => 'https://basecamp.com/999999999/api/v1/projects/605816632-bcx/todos/223304243-design-it.json',
                    'creator' => [
                        'id' => 149087659,
                        'name' => 'Jason Fried',
                    ],
                ],
                [
                    'id' => 1054456335,
                    'created_at' => '2012-03-24T11:00:44-05:00',
                    'summary' => 'added a to-do: t',
                    'url' => 'https://basecamp.com/999999999/api/v1/projects/605816632-bcx/todos/1046098402-t.json',
                    'creator' => [
                        'id' => 149087659,
                        'name' => 'Jason Fried',
                    ],
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new EventsResource($client);

        $events = $resource->allInProject(123456);

        $this->assertIsArray($events);
        $this->assertCount(2, $events);
        $this->assertSame(1054456336, $events[0]['id']);
        $this->assertSame('re-assigned a to-do to Funky ones: Design it', $events[0]['summary']);
        $this->assertArrayHasKey('creator', $events[0]);
        $this->assertArrayHasKey('url', $events[0]);
    }

    public function testAllInProjectWithSince(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                ['id' => 1, 'summary' => 'Recent event'],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new EventsResource($client);

        $events = $resource->allInProject(123456, ['since' => '2012-03-24T11:00:00-05:00']);

        $this->assertIsArray($events);
        $this->assertCount(1, $events);
    }

    public function testAllByPerson(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'summary' => 'created a message',
                    'created_at' => '2012-03-24T11:00:50-05:00',
                    'creator' => [
                        'id' => 149087659,
                        'name' => 'Jason Fried',
                    ],
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new EventsResource($client);

        $events = $resource->allByPerson(149087659);

        $this->assertIsArray($events);
        $this->assertCount(1, $events);
        $this->assertSame(1, $events[0]['id']);
        $this->assertSame('created a message', $events[0]['summary']);
        $this->assertArrayHasKey('creator', $events[0]);
    }

    public function testAllByPersonWithFilters(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                ['id' => 10, 'summary' => 'Recent person event'],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new EventsResource($client);

        $events = $resource->allByPerson(149087659, [
            'since' => '2012-03-24T11:00:00-05:00',
            'page' => 1,
        ]);

        $this->assertIsArray($events);
        $this->assertCount(1, $events);
    }
}
