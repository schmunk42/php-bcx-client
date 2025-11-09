<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Resource;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Resource\PeopleResource;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class PeopleResourceTest extends TestCase
{
    private PeopleResource $resource;
    private BasecampClient $client;

    protected function setUp(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 149087659,
                    'name' => 'Jason Fried',
                    'email_address' => 'jason@basecamp.com',
                ],
            ])),
        ]);

        $this->client = new BasecampClient('999999999', $auth, $httpClient);
        $this->resource = new PeopleResource($this->client);
    }

    public function testAll(): void
    {
        $people = $this->resource->all();

        $this->assertIsArray($people);
        $this->assertCount(1, $people);
        $this->assertSame(149087659, $people[0]['id']);
        $this->assertSame('Jason Fried', $people[0]['name']);
        $this->assertSame('jason@basecamp.com', $people[0]['email_address']);
    }

    public function testInProject(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'name' => 'John Doe',
                    'email_address' => 'john@example.com',
                ],
                [
                    'id' => 2,
                    'name' => 'Jane Smith',
                    'email_address' => 'jane@example.com',
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new PeopleResource($client);

        $people = $resource->inProject(123456);

        $this->assertIsArray($people);
        $this->assertCount(2, $people);
        $this->assertSame(1, $people[0]['id']);
        $this->assertSame('John Doe', $people[0]['name']);
    }

    public function testGet(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 149087659,
                'name' => 'Jason Fried',
                'email_address' => 'jason@basecamp.com',
                'admin' => true,
                'created_at' => '2012-03-22T16:56:48-05:00',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new PeopleResource($client);

        $person = $resource->get(149087659);

        $this->assertIsArray($person);
        $this->assertSame(149087659, $person['id']);
        $this->assertSame('Jason Fried', $person['name']);
        $this->assertSame('jason@basecamp.com', $person['email_address']);
        $this->assertTrue($person['admin']);
    }

    public function testMe(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 1,
                'name' => 'Richard van den Brand',
                'email_address' => 'richard@example.com',
                'admin' => false,
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new PeopleResource($client);

        $me = $resource->me();

        $this->assertIsArray($me);
        $this->assertSame(1, $me['id']);
        $this->assertSame('Richard van den Brand', $me['name']);
        $this->assertArrayHasKey('email_address', $me);
    }

    public function testGrantAccess(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 1,
                'identity_id' => 1,
                'name' => 'John Doe',
                'email_address' => 'john@example.com',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new PeopleResource($client);

        $access = $resource->grantAccess(123456, [
            'person_id' => 987654,
        ]);

        $this->assertIsArray($access);
        $this->assertSame(1, $access['id']);
        $this->assertSame(1, $access['identity_id']);
        $this->assertArrayHasKey('email_address', $access);
    }

    public function testRevokeAccess(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 204]),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new PeopleResource($client);

        // Should not throw an exception
        $resource->revokeAccess(123456, 987654);

        $this->assertTrue(true); // Assert test reached this point
    }

    public function testUpdateAccess(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 1,
                'identity_id' => 987654,
                'name' => 'John Doe',
                'email_address' => 'john@example.com',
                'admin' => true,
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new PeopleResource($client);

        $access = $resource->updateAccess(123456, 987654, [
            'admin' => true,
        ]);

        $this->assertIsArray($access);
        $this->assertSame(1, $access['id']);
        $this->assertSame(987654, $access['identity_id']);
        $this->assertTrue($access['admin']);
    }

    public function testGetTrashed(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'name' => 'Deleted User',
                    'email_address' => 'deleted@example.com',
                    'trashed' => true,
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new PeopleResource($client);

        $trashed = $resource->getTrashed();

        $this->assertIsArray($trashed);
        $this->assertCount(1, $trashed);
        $this->assertSame(1, $trashed[0]['id']);
        $this->assertTrue($trashed[0]['trashed']);
    }

    public function testGetAssignedTodos(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'name' => 'My Todolist',
                    'assigned_todos' => [
                        [
                            'id' => 101,
                            'content' => 'Task 1',
                            'completed' => false,
                        ],
                        [
                            'id' => 102,
                            'content' => 'Task 2',
                            'completed' => true,
                        ],
                    ],
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new PeopleResource($client);

        $todolists = $resource->getAssignedTodos(149087659);

        $this->assertIsArray($todolists);
        $this->assertCount(1, $todolists);
        $this->assertSame(1, $todolists[0]['id']);
        $this->assertArrayHasKey('assigned_todos', $todolists[0]);
        $this->assertCount(2, $todolists[0]['assigned_todos']);
    }

    public function testGetAssignedTodosWithDueSince(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'name' => 'My Todolist',
                    'assigned_todos' => [
                        [
                            'id' => 101,
                            'content' => 'Upcoming Task',
                            'due_at' => '2025-12-31',
                        ],
                    ],
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new PeopleResource($client);

        $todolists = $resource->getAssignedTodos(149087659, '2025-01-01');

        $this->assertIsArray($todolists);
        $this->assertCount(1, $todolists);
        $this->assertArrayHasKey('assigned_todos', $todolists[0]);
    }

    public function testGetEvents(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'action' => 'created',
                    'target' => 'Todo',
                    'created_at' => '2025-01-01T12:00:00Z',
                ],
                [
                    'id' => 2,
                    'action' => 'updated',
                    'target' => 'Message',
                    'created_at' => '2025-01-02T14:30:00Z',
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new PeopleResource($client);

        $events = $resource->getEvents(149087659);

        $this->assertIsArray($events);
        $this->assertCount(2, $events);
        $this->assertSame(1, $events[0]['id']);
        $this->assertSame('created', $events[0]['action']);
        $this->assertSame('Todo', $events[0]['target']);
    }

    public function testGetProjects(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'name' => 'Project Alpha',
                    'description' => 'First project',
                ],
                [
                    'id' => 2,
                    'name' => 'Project Beta',
                    'description' => 'Second project',
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new PeopleResource($client);

        $projects = $resource->getProjects(149087659);

        $this->assertIsArray($projects);
        $this->assertCount(2, $projects);
        $this->assertSame(1, $projects[0]['id']);
        $this->assertSame('Project Alpha', $projects[0]['name']);
        $this->assertSame(2, $projects[1]['id']);
        $this->assertSame('Project Beta', $projects[1]['name']);
    }
}
