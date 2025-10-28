<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Client;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Exception\AuthenticationException;
use Schmunk42\BasecampApi\Exception\RequestException;
use Schmunk42\BasecampApi\Resource\PeopleResource;
use Schmunk42\BasecampApi\Resource\ProjectsResource;
use Schmunk42\BasecampApi\Resource\TodolistsResource;
use Schmunk42\BasecampApi\Resource\TodosResource;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class BasecampClientTest extends TestCase
{
    private BasecampClient $client;

    protected function setUp(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse('{"id": 1}', ['http_code' => 200]),
        ]);

        $this->client = new BasecampClient('999999999', $auth, $httpClient);
    }

    public function testGetAccountId(): void
    {
        $this->assertSame('999999999', $this->client->getAccountId());
    }

    public function testProjectsReturnsProjectsResource(): void
    {
        $this->assertInstanceOf(ProjectsResource::class, $this->client->projects());
    }

    public function testTodolistsReturnsTodolistsResource(): void
    {
        $this->assertInstanceOf(TodolistsResource::class, $this->client->todolists());
    }

    public function testTodosReturnsTodosResource(): void
    {
        $this->assertInstanceOf(TodosResource::class, $this->client->todos());
    }

    public function testPeopleReturnsPeopleResource(): void
    {
        $this->assertInstanceOf(PeopleResource::class, $this->client->people());
    }

    public function testGetRequest(): void
    {
        $data = $this->client->get('/projects.json');

        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame(1, $data['id']);
    }

    public function testExpiredAuthenticationThrowsException(): void
    {
        $expiredAuth = new OAuth2Authentication(
            'expired-token',
            (new \DateTimeImmutable())->modify('-1 hour')
        );

        $client = new BasecampClient('999999999', $expiredAuth);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Authentication token is invalid or expired');

        $client->get('/projects.json');
    }

    public function testUnauthorizedResponseThrowsAuthenticationException(): void
    {
        $auth = new OAuth2Authentication('invalid-token');
        $httpClient = new MockHttpClient([
            new MockResponse('{"error": "Unauthorized"}', ['http_code' => 401]),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);

        $this->expectException(AuthenticationException::class);

        $client->get('/projects.json');
    }

    public function testBadRequestThrowsRequestException(): void
    {
        $auth = new OAuth2Authentication('valid-token');
        $httpClient = new MockHttpClient([
            new MockResponse('{"error": "Bad Request"}', ['http_code' => 400]),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);

        $this->expectException(RequestException::class);

        $client->get('/projects.json');
    }

    public function testDeleteRequestReturnsEmptyArray(): void
    {
        $auth = new OAuth2Authentication('valid-token');
        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 204]),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);

        $client->delete('/projects/123.json');

        // If we get here without exception, the test passes
        $this->assertTrue(true);
    }
}
