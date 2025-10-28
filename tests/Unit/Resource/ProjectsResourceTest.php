<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Resource;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Resource\ProjectsResource;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ProjectsResourceTest extends TestCase
{
    private ProjectsResource $resource;

    protected function setUp(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                ['id' => 1, 'name' => 'Project 1'],
                ['id' => 2, 'name' => 'Project 2'],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $this->resource = new ProjectsResource($client);
    }

    public function testAll(): void
    {
        $projects = $this->resource->all();

        $this->assertIsArray($projects);
        $this->assertCount(2, $projects);
        $this->assertSame('Project 1', $projects[0]['name']);
    }

    public function testGet(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode(['id' => 123, 'name' => 'Test Project'])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new ProjectsResource($client);

        $project = $resource->get(123);

        $this->assertIsArray($project);
        $this->assertSame(123, $project['id']);
        $this->assertSame('Test Project', $project['name']);
    }

    public function testCreate(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode(['id' => 456, 'name' => 'New Project'])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new ProjectsResource($client);

        $project = $resource->create(['name' => 'New Project']);

        $this->assertIsArray($project);
        $this->assertSame(456, $project['id']);
    }
}
