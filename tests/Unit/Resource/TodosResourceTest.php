<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Resource;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Resource\TodosResource;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class TodosResourceTest extends TestCase
{
    private TodosResource $resource;
    private BasecampClient $client;

    protected function setUp(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'content' => 'Design it',
                    'completed' => false,
                    'todolist_id' => 1000,
                ],
            ])),
        ]);

        $this->client = new BasecampClient('999999999', $auth, $httpClient);
        $this->resource = new TodosResource($this->client);
    }

    public function testAll(): void
    {
        $todos = $this->resource->all(123456, 789);

        $this->assertIsArray($todos);
        $this->assertCount(1, $todos);
        $this->assertSame(1, $todos[0]['id']);
        $this->assertSame('Design it', $todos[0]['content']);
        $this->assertFalse($todos[0]['completed']);
    }

    public function testGet(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 1,
                'todolist_id' => 1000,
                'content' => 'Design it',
                'completed' => false,
                'due_at' => '2012-03-27',
                'comments_count' => 1,
                'creator' => [
                    'id' => 127326141,
                    'name' => 'David Heinemeier Hansson',
                ],
                'assignee' => [
                    'id' => 149087659,
                    'type' => 'Person',
                    'name' => 'Jason Fried',
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodosResource($client);

        $todo = $resource->get(123456, 1);

        $this->assertIsArray($todo);
        $this->assertSame(1, $todo['id']);
        $this->assertSame('Design it', $todo['content']);
        $this->assertSame(1000, $todo['todolist_id']);
        $this->assertArrayHasKey('creator', $todo);
        $this->assertArrayHasKey('assignee', $todo);
    }

    public function testCreate(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 41361256,
                'content' => 'New task',
                'completed' => false,
                'todolist_id' => 7091994,
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodosResource($client);

        $todo = $resource->create(123456, 7091994, [
            'content' => 'New task',
        ]);

        $this->assertIsArray($todo);
        $this->assertSame(41361256, $todo['id']);
        $this->assertSame('New task', $todo['content']);
        $this->assertFalse($todo['completed']);
    }

    public function testUpdate(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 123,
                'content' => 'Updated task',
                'completed' => false,
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodosResource($client);

        $todo = $resource->update(123456, 123, [
            'content' => 'Updated task',
        ]);

        $this->assertIsArray($todo);
        $this->assertSame(123, $todo['id']);
        $this->assertSame('Updated task', $todo['content']);
    }

    public function testDelete(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 204]),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodosResource($client);

        // Should not throw an exception
        $resource->delete(123456, 789);

        $this->assertTrue(true); // Assert test reached this point
    }

    public function testComplete(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 123,
                'content' => 'Task to complete',
                'completed' => true,
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodosResource($client);

        $todo = $resource->complete(123456, 123);

        $this->assertIsArray($todo);
        $this->assertSame(123, $todo['id']);
        $this->assertTrue($todo['completed']);
    }

    public function testUncomplete(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 123,
                'content' => 'Task to uncomplete',
                'completed' => false,
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodosResource($client);

        $todo = $resource->uncomplete(123456, 123);

        $this->assertIsArray($todo);
        $this->assertSame(123, $todo['id']);
        $this->assertFalse($todo['completed']);
    }

    public function testAllInProject(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'content' => 'Todo from list A',
                    'completed' => false,
                ],
                [
                    'id' => 2,
                    'content' => 'Todo from list B',
                    'completed' => true,
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodosResource($client);

        $todos = $resource->allInProject(123456);

        $this->assertIsArray($todos);
        $this->assertCount(2, $todos);
        $this->assertSame(1, $todos[0]['id']);
        $this->assertSame(2, $todos[1]['id']);
    }

    public function testAllInProjectWithDueSince(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'content' => 'Upcoming todo',
                    'due_at' => '2025-12-31',
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodosResource($client);

        $todos = $resource->allInProject(123456, '2025-01-01');

        $this->assertIsArray($todos);
        $this->assertCount(1, $todos);
        $this->assertSame(1, $todos[0]['id']);
    }

    public function testGetCompleted(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'content' => 'Completed task',
                    'completed' => true,
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodosResource($client);

        $todos = $resource->getCompleted(123456, 789);

        $this->assertIsArray($todos);
        $this->assertCount(1, $todos);
        $this->assertSame(1, $todos[0]['id']);
        $this->assertTrue($todos[0]['completed']);
    }

    public function testGetRemaining(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'content' => 'Active task',
                    'completed' => false,
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodosResource($client);

        $todos = $resource->getRemaining(123456, 789);

        $this->assertIsArray($todos);
        $this->assertCount(1, $todos);
        $this->assertSame(1, $todos[0]['id']);
        $this->assertFalse($todos[0]['completed']);
    }

    public function testGetTrashed(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'content' => 'Deleted task',
                    'trashed' => true,
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodosResource($client);

        $todos = $resource->getTrashed(123456, 789);

        $this->assertIsArray($todos);
        $this->assertCount(1, $todos);
        $this->assertSame(1, $todos[0]['id']);
        $this->assertTrue($todos[0]['trashed']);
    }

    public function testGetAllCompletedInProject(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'content' => 'Completed task 1',
                    'completed' => true,
                ],
                [
                    'id' => 2,
                    'content' => 'Completed task 2',
                    'completed' => true,
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodosResource($client);

        $todos = $resource->getAllCompletedInProject(123456);

        $this->assertIsArray($todos);
        $this->assertCount(2, $todos);
        $this->assertTrue($todos[0]['completed']);
        $this->assertTrue($todos[1]['completed']);
    }

    public function testGetAllRemainingInProject(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'content' => 'Active task 1',
                    'completed' => false,
                ],
                [
                    'id' => 2,
                    'content' => 'Active task 2',
                    'completed' => false,
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new TodosResource($client);

        $todos = $resource->getAllRemainingInProject(123456);

        $this->assertIsArray($todos);
        $this->assertCount(2, $todos);
        $this->assertFalse($todos[0]['completed']);
        $this->assertFalse($todos[1]['completed']);
    }
}
