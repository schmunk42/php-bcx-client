<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Resource;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Resource\GroupsResource;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class GroupsResourceTest extends TestCase
{
    private GroupsResource $resource;
    private BasecampClient $client;

    protected function setUp(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 12345,
                    'name' => 'Foo Limited',
                    'created_at' => '2015-03-25T13:37:59.000Z',
                    'updated_at' => '2015-03-25T13:38:13.000Z',
                ],
                [
                    'id' => 67890,
                    'name' => 'Bar Associates',
                    'created_at' => '2015-03-25T13:37:59.000Z',
                    'updated_at' => '2015-03-25T13:38:34.000Z',
                ],
            ])),
        ]);

        $this->client = new BasecampClient('999999999', $auth, $httpClient);
        $this->resource = new GroupsResource($this->client);
    }

    public function testAll(): void
    {
        $groups = $this->resource->all();

        $this->assertIsArray($groups);
        $this->assertCount(2, $groups);
        $this->assertSame(12345, $groups[0]['id']);
        $this->assertSame('Foo Limited', $groups[0]['name']);
        $this->assertArrayHasKey('created_at', $groups[0]);
        $this->assertArrayHasKey('updated_at', $groups[0]);
    }

    public function testGet(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 12345,
                'name' => 'Foo Limited',
                'created_at' => '2015-03-25T13:37:59.000Z',
                'updated_at' => '2015-03-25T13:38:13.000Z',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new GroupsResource($client);

        $group = $resource->get(12345);

        $this->assertIsArray($group);
        $this->assertSame(12345, $group['id']);
        $this->assertSame('Foo Limited', $group['name']);
        $this->assertArrayHasKey('created_at', $group);
        $this->assertArrayHasKey('updated_at', $group);
    }
}
