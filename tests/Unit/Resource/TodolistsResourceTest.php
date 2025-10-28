<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Resource;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Resource\TodolistsResource;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class TodolistsResourceTest extends TestCase
{
    private TodolistsResource $resource;
    private BasecampClient $client;

    protected function setUp(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 3,
                    'name' => 'Todo List 1',
                    'description' => null,
                    'completed' => false,
                    'remaining_count' => 2,
                    'completed_count' => 0,
                ],
            ])),
        ]);

        $this->client = new BasecampClient('999999999', $auth, $httpClient);
        $this->resource = new TodolistsResource($this->client);
    }

    public function testAll(): void
    {
        $todolists = $this->resource->all(123456);

        $this->assertIsArray($todolists);
        $this->assertCount(1, $todolists);
        $this->assertSame(3, $todolists[0]['id']);
        $this->assertSame('Todo List 1', $todolists[0]['name']);
        $this->assertFalse($todolists[0]['completed']);
    }

    public function testCompleted(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 7091994,
                    'name' => 'Support (inbound)',
                    'description' => 'Lorem ipsum',
                    'completed' => true,
                    'remaining_count' => 0,
                    'completed_count' => 5,
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodolistsResource($client);

        $todolists = $resource->completed(123456);

        $this->assertIsArray($todolists);
        $this->assertCount(1, $todolists);
        $this->assertSame(7091994, $todolists[0]['id']);
        $this->assertSame('Support (inbound)', $todolists[0]['name']);
        $this->assertTrue($todolists[0]['completed']);
        $this->assertSame(5, $todolists[0]['completed_count']);
    }

    public function testAssigned(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 968316918,
                    'name' => 'My Assigned Todolist',
                    'assigned_todos' => [
                        [
                            'id' => 223304243,
                            'content' => 'Complete this task',
                        ],
                    ],
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodolistsResource($client);

        $todolists = $resource->assigned();

        $this->assertIsArray($todolists);
        $this->assertCount(1, $todolists);
        $this->assertSame(968316918, $todolists[0]['id']);
        $this->assertArrayHasKey('assigned_todos', $todolists[0]);
        $this->assertIsArray($todolists[0]['assigned_todos']);
    }

    public function testGet(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 1,
                'name' => 'Specific Todolist',
                'description' => 'Test description',
                'todos' => [
                    'remaining' => [
                        ['id' => 1, 'content' => 'Task 1'],
                        ['id' => 2, 'content' => 'Task 2'],
                    ],
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodolistsResource($client);

        $todolist = $resource->get(123456, 1);

        $this->assertIsArray($todolist);
        $this->assertSame(1, $todolist['id']);
        $this->assertSame('Specific Todolist', $todolist['name']);
        $this->assertArrayHasKey('todos', $todolist);
        $this->assertArrayHasKey('remaining', $todolist['todos']);
    }

    public function testCreate(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 7091994,
                'name' => 'New Todolist',
                'description' => 'New description',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodolistsResource($client);

        $todolist = $resource->create(123456, [
            'name' => 'New Todolist',
            'description' => 'New description',
        ]);

        $this->assertIsArray($todolist);
        $this->assertSame(7091994, $todolist['id']);
        $this->assertSame('New Todolist', $todolist['name']);
        $this->assertSame('New description', $todolist['description']);
    }

    public function testUpdate(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 123,
                'name' => 'Updated Todolist',
                'description' => 'Updated description',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodolistsResource($client);

        $todolist = $resource->update(123456, 123, [
            'name' => 'Updated Todolist',
        ]);

        $this->assertIsArray($todolist);
        $this->assertSame(123, $todolist['id']);
        $this->assertSame('Updated Todolist', $todolist['name']);
    }

    public function testDelete(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 204]),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodolistsResource($client);

        // Should not throw an exception
        $resource->delete(123456, 789);

        $this->assertTrue(true); // Assert test reached this point
    }
}
