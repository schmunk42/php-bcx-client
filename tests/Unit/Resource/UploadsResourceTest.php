<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Resource;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;
use Schmunk42\BasecampApi\Client\BasecampClient;
use Schmunk42\BasecampApi\Resource\UploadsResource;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class UploadsResourceTest extends TestCase
{
    private UploadsResource $resource;
    private BasecampClient $client;

    protected function setUp(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                [
                    'id' => 1,
                    'key' => '93e10dacd3aa64ab2edde55642c751f1e7b2557e',
                    'name' => 'test.pdf',
                ],
            ])),
        ]);

        $this->client = new BasecampClient('999999999', $auth, $httpClient);
        $this->resource = new UploadsResource($this->client);
    }

    public function testAll(): void
    {
        $attachments = $this->resource->all(123456);

        $this->assertIsArray($attachments);
        $this->assertCount(1, $attachments);
        $this->assertSame(1, $attachments[0]['id']);
        $this->assertSame('93e10dacd3aa64ab2edde55642c751f1e7b2557e', $attachments[0]['key']);
    }

    public function testAllWithPagination(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                ['id' => 1, 'name' => 'file1.pdf'],
                ['id' => 2, 'name' => 'file2.pdf'],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new UploadsResource($client);

        $attachments = $resource->all(123456, ['page' => 2]);

        $this->assertIsArray($attachments);
        $this->assertCount(2, $attachments);
    }

    public function testAllGlobal(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                ['id' => 1, 'name' => 'file1.pdf'],
                ['id' => 2, 'name' => 'file2.pdf'],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new UploadsResource($client);

        $attachments = $resource->allGlobal(['sort' => 'newest']);

        $this->assertIsArray($attachments);
        $this->assertCount(2, $attachments);
    }

    public function testGet(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 123,
                'key' => '93e10dacd3aa64ab2edde55642c751f1e7b2557e',
                'name' => 'document.pdf',
                'byte_size' => 1024000,
                'content_type' => 'application/pdf',
                'created_at' => '2012-03-22T16:56:48-05:00',
                'url' => 'https://example.com/file.pdf',
                'creator' => [
                    'id' => 149087659,
                    'name' => 'Jason Fried',
                ],
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new UploadsResource($client);

        $attachment = $resource->get(123456, 123);

        $this->assertIsArray($attachment);
        $this->assertSame(123, $attachment['id']);
        $this->assertSame('document.pdf', $attachment['name']);
        $this->assertSame('application/pdf', $attachment['content_type']);
        $this->assertSame(1024000, $attachment['byte_size']);
        $this->assertArrayHasKey('creator', $attachment);
    }

    public function testCreate(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'token' => '51800634-9aecec5cfd6acf939b08cd1957a3c12796ae05fa',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new UploadsResource($client);

        $result = $resource->create('binary file content', 'image/jpeg');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertSame('51800634-9aecec5cfd6acf939b08cd1957a3c12796ae05fa', $result['token']);
    }

    public function testCreatePDF(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'token' => 'abc123-pdf-token',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new UploadsResource($client);

        $pdfContent = '%PDF-1.4 fake pdf content';
        $result = $resource->create($pdfContent, 'application/pdf');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertSame('abc123-pdf-token', $result['token']);
    }

    public function testUpdate(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 123,
                'name' => 'renamed-file.pdf',
                'updated_at' => '2012-03-24T10:56:33-05:00',
            ])),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new UploadsResource($client);

        $attachment = $resource->update(123456, 123, [
            'name' => 'renamed-file.pdf',
        ]);

        $this->assertIsArray($attachment);
        $this->assertSame(123, $attachment['id']);
        $this->assertSame('renamed-file.pdf', $attachment['name']);
    }

    public function testDelete(): void
    {
        $auth = new OAuth2Authentication('test-token');
        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 204]),
        ]);

        $client = new BasecampClient('999999999', $auth, $httpClient);
        $resource = new UploadsResource($client);

        // Should not throw an exception
        $resource->delete(123456, 789);

        $this->assertTrue(true); // Assert test reached this point
    }
}
