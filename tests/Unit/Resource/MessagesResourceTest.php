<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Resource;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Resource\MessagesResource;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class MessagesResourceTest extends TestCase
{
    private MessagesResource $resource;
    private BasecampClient $client;

    protected function setUp(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 1,
                'subject' => 'Test Message',
                'content' => 'Message content',
            ])),
        ]);

        $this->client = new BasecampClient('999999999', $auth, $httpClient);
        $this->resource = new MessagesResource($this->client);
    }

    public function testGet(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 123,
                'subject' => 'Project Update',
                'content' => '<div>This is the message content</div>',
                'created_at' => '2012-03-22T16:56:48-05:00',
                'updated_at' => '2012-03-22T16:56:48-05:00',
                'comments_count' => 5,
                'creator' => [
                    'id' => 149087659,
                    'name' => 'Jason Fried',
                ],
                'subscribers' => [
                    ['id' => 149087659, 'name' => 'Jason Fried'],
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new MessagesResource($client);

        $message = $resource->get(123456, 123);

        $this->assertIsArray($message);
        $this->assertSame(123, $message['id']);
        $this->assertSame('Project Update', $message['subject']);
        $this->assertSame('<div>This is the message content</div>', $message['content']);
        $this->assertArrayHasKey('creator', $message);
        $this->assertArrayHasKey('subscribers', $message);
        $this->assertSame(5, $message['comments_count']);
    }

    public function testCreate(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 456,
                'subject' => 'New Message',
                'content' => 'This is a new message',
                'created_at' => '2012-03-24T09:53:35-05:00',
                'updated_at' => '2012-03-24T09:53:35-05:00',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new MessagesResource($client);

        $message = $resource->create(123456, [
            'subject' => 'New Message',
            'content' => 'This is a new message',
        ]);

        $this->assertIsArray($message);
        $this->assertSame(456, $message['id']);
        $this->assertSame('New Message', $message['subject']);
        $this->assertSame('This is a new message', $message['content']);
    }

    public function testUpdate(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 123,
                'subject' => 'Updated Message',
                'content' => 'Updated content',
                'updated_at' => '2012-03-24T10:56:33-05:00',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new MessagesResource($client);

        $message = $resource->update(123456, 123, [
            'subject' => 'Updated Message',
            'content' => 'Updated content',
        ]);

        $this->assertIsArray($message);
        $this->assertSame(123, $message['id']);
        $this->assertSame('Updated Message', $message['subject']);
        $this->assertSame('Updated content', $message['content']);
    }

    public function testDelete(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 204]),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new MessagesResource($client);

        // Should not throw an exception
        $resource->delete(123456, 789);

        $this->assertTrue(true); // Assert test reached this point
    }
}
