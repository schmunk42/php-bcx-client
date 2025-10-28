<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Resource;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Resource\CommentsResource;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class CommentsResourceTest extends TestCase
{
    private CommentsResource $resource;
    private BasecampClient $client;

    protected function setUp(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 1,
                'content' => 'Test comment',
            ])),
        ]);

        $this->client = new BasecampClient('999999999', $auth, $httpClient);
        $this->resource = new CommentsResource($this->client);
    }

    public function testCreateOnTodo(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 61775464,
                'content' => 'Text message.',
                'created_at' => '2013-04-30T16:45:51.562+02:00',
                'updated_at' => '2013-04-30T16:45:51.562+02:00',
                'attachments' => [],
                'creator' => [
                    'id' => 4448817,
                    'name' => 'Reinier Kip',
                    'avatar_url' => 'http://example.com/avatar.gif',
                ],
                'topic_url' => 'https://basecamp.com/9999999/api/v1/projects/1-support/todos/41367037-subject.json',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new CommentsResource($client);

        $comment = $resource->create(123456, 'todos', 41367037, [
            'content' => 'Text message.',
        ]);

        $this->assertIsArray($comment);
        $this->assertSame(61775464, $comment['id']);
        $this->assertSame('Text message.', $comment['content']);
        $this->assertArrayHasKey('creator', $comment);
        $this->assertArrayHasKey('topic_url', $comment);
        $this->assertIsArray($comment['attachments']);
    }

    public function testCreateOnMessage(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 123,
                'content' => 'Great update!',
                'created_at' => '2012-03-22T16:56:48-05:00',
                'updated_at' => '2012-03-22T16:56:48-05:00',
                'creator' => [
                    'id' => 149087659,
                    'name' => 'Jason Fried',
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new CommentsResource($client);

        $comment = $resource->create(123456, 'messages', 789, [
            'content' => 'Great update!',
        ]);

        $this->assertIsArray($comment);
        $this->assertSame(123, $comment['id']);
        $this->assertSame('Great update!', $comment['content']);
        $this->assertArrayHasKey('creator', $comment);
    }

    public function testCreateWithSubscribers(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 456,
                'content' => 'Tagged comment',
                'subscribers' => [
                    ['id' => 149087659, 'name' => 'Jason Fried'],
                    ['id' => 1071630348, 'name' => 'Jeremy Kemper'],
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new CommentsResource($client);

        $comment = $resource->create(123456, 'todos', 789, [
            'content' => 'Tagged comment',
            'subscribers' => [149087659, 1071630348],
        ]);

        $this->assertIsArray($comment);
        $this->assertSame(456, $comment['id']);
        $this->assertArrayHasKey('subscribers', $comment);
        $this->assertCount(2, $comment['subscribers']);
    }

    public function testDelete(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 204]),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new CommentsResource($client);

        // Should not throw an exception
        $resource->delete(123456, 789);

        $this->assertTrue(true); // Assert test reached this point
    }
}
