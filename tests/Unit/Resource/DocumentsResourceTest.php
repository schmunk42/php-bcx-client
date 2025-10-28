<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Resource;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Resource\DocumentsResource;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class DocumentsResourceTest extends TestCase
{
    private DocumentsResource $resource;
    private BasecampClient $client;

    protected function setUp(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 12343,
                    'title' => 'APPS',
                    'updated_at' => '2012-09-19T08:34:56.000+02:00',
                ],
            ])),
        ]);

        $this->client = new BasecampClient('999999999', $auth, $httpClient);
        $this->resource = new DocumentsResource($this->client);
    }

    public function testAll(): void
    {
        $documents = $this->resource->all(123456);

        $this->assertIsArray($documents);
        $this->assertCount(1, $documents);
        $this->assertSame(12343, $documents[0]['id']);
        $this->assertSame('APPS', $documents[0]['title']);
    }

    public function testAllWithSorting(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                ['id' => 1, 'title' => 'Alpha Document'],
                ['id' => 2, 'title' => 'Beta Document'],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new DocumentsResource($client);

        $documents = $resource->all(123456, ['sort' => 'az']);

        $this->assertIsArray($documents);
        $this->assertCount(2, $documents);
    }

    public function testAllGlobal(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                ['id' => 1, 'title' => 'Doc 1'],
                ['id' => 2, 'title' => 'Doc 2'],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new DocumentsResource($client);

        $documents = $resource->allGlobal();

        $this->assertIsArray($documents);
        $this->assertCount(2, $documents);
    }

    public function testGet(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 123,
                'title' => 'APPS',
                'content' => 'lorem ipsum',
                'created_at' => '2012-08-28T19:31:57.000+02:00',
                'updated_at' => '2012-09-19T08:34:56.000+02:00',
                'last_updater' => [
                    'id' => 344,
                    'name' => 'Joe Bar',
                ],
                'comments' => [],
                'subscribers' => [
                    ['id' => 2, 'name' => 'Foo Bar'],
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new DocumentsResource($client);

        $document = $resource->get(123456, 123);

        $this->assertIsArray($document);
        $this->assertSame(123, $document['id']);
        $this->assertSame('APPS', $document['title']);
        $this->assertSame('lorem ipsum', $document['content']);
        $this->assertArrayHasKey('last_updater', $document);
        $this->assertArrayHasKey('subscribers', $document);
    }

    public function testCreate(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 456,
                'title' => 'New Document',
                'content' => 'Document content',
                'created_at' => '2012-03-24T09:53:35-05:00',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new DocumentsResource($client);

        $document = $resource->create(123456, [
            'title' => 'New Document',
            'content' => 'Document content',
        ]);

        $this->assertIsArray($document);
        $this->assertSame(456, $document['id']);
        $this->assertSame('New Document', $document['title']);
        $this->assertSame('Document content', $document['content']);
    }

    public function testUpdate(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 123,
                'title' => 'Updated Document',
                'content' => 'Updated content',
                'updated_at' => '2012-03-24T10:56:33-05:00',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new DocumentsResource($client);

        $document = $resource->update(123456, 123, [
            'title' => 'Updated Document',
            'content' => 'Updated content',
        ]);

        $this->assertIsArray($document);
        $this->assertSame(123, $document['id']);
        $this->assertSame('Updated Document', $document['title']);
        $this->assertSame('Updated content', $document['content']);
    }

    public function testDelete(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 204]),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new DocumentsResource($client);

        // Should not throw an exception
        $resource->delete(123456, 789);

        $this->assertTrue(true); // Assert test reached this point
    }
}
