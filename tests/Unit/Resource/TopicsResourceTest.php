<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Resource;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Resource\TopicsResource;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class TopicsResourceTest extends TestCase
{
    private TopicsResource $resource;
    private BasecampClient $client;

    protected function setUp(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 3,
                    'title' => 'Verslag meeting 11 april',
                    'excerpt' => 'Vandaag weer een nuttige bijeenkomst gehad...',
                    'created_at' => '2013-04-11T16:40:09.000+02:00',
                    'updated_at' => '2013-04-11T16:40:09.000+02:00',
                    'attachments' => 1,
                    'last_updater' => [
                        'id' => 5,
                        'name' => 'Foo Bar',
                    ],
                    'topicable' => [
                        'id' => 4,
                        'type' => 'Message',
                        'url' => 'https://basecamp.com/1/api/v1/projects/2/messages/4.json',
                    ],
                ],
            ])),
        ]);

        $this->client = new BasecampClient('999999999', $auth, $httpClient);
        $this->resource = new TopicsResource($this->client);
    }

    public function testAllInProject(): void
    {
        $topics = $this->resource->allInProject(123);

        $this->assertIsArray($topics);
        $this->assertCount(1, $topics);
        $this->assertSame(3, $topics[0]['id']);
        $this->assertSame('Verslag meeting 11 april', $topics[0]['title']);
        $this->assertArrayHasKey('excerpt', $topics[0]);
        $this->assertArrayHasKey('last_updater', $topics[0]);
        $this->assertArrayHasKey('topicable', $topics[0]);
    }

    public function testAllInProjectWithPagination(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 51,
                    'title' => 'Topic 51',
                    'excerpt' => 'Page 2 topic',
                    'topicable' => [
                        'type' => 'Todo',
                        'id' => 100,
                    ],
                ],
                [
                    'id' => 52,
                    'title' => 'Topic 52',
                    'excerpt' => 'Another page 2 topic',
                    'topicable' => [
                        'type' => 'Document',
                        'id' => 200,
                    ],
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TopicsResource($client);

        $topics = $resource->allInProject(123, ['page' => 2]);

        $this->assertIsArray($topics);
        $this->assertCount(2, $topics);
        $this->assertSame(51, $topics[0]['id']);
        $this->assertSame('Topic 51', $topics[0]['title']);
    }

    public function testTopicableTypes(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'title' => 'Message Topic',
                    'topicable' => ['type' => 'Message', 'id' => 10],
                ],
                [
                    'id' => 2,
                    'title' => 'Todo Topic',
                    'topicable' => ['type' => 'Todo', 'id' => 20],
                ],
                [
                    'id' => 3,
                    'title' => 'Document Topic',
                    'topicable' => ['type' => 'Document', 'id' => 30],
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TopicsResource($client);

        $topics = $resource->allInProject(123);

        $this->assertCount(3, $topics);
        $this->assertSame('Message', $topics[0]['topicable']['type']);
        $this->assertSame('Todo', $topics[1]['topicable']['type']);
        $this->assertSame('Document', $topics[2]['topicable']['type']);
    }
}
