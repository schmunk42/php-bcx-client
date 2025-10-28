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
}
