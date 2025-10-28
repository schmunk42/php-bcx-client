<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Exception\RequestException;

final class RequestExceptionTest extends TestCase
{
    public function testExceptionWithStatusCodeAndResponseBody(): void
    {
        $exception = new RequestException(
            'Request failed',
            400,
            '{"error": "Bad Request"}'
        );

        $this->assertSame('Request failed', $exception->getMessage());
        $this->assertSame(400, $exception->getStatusCode());
        $this->assertSame('{"error": "Bad Request"}', $exception->getResponseBody());
        $this->assertSame(400, $exception->getCode());
    }

    public function testExceptionWithoutResponseBody(): void
    {
        $exception = new RequestException('Request failed', 500);

        $this->assertSame('Request failed', $exception->getMessage());
        $this->assertSame(500, $exception->getStatusCode());
        $this->assertNull($exception->getResponseBody());
    }

    public function testExceptionWithDefaultValues(): void
    {
        $exception = new RequestException('Request failed');

        $this->assertSame('Request failed', $exception->getMessage());
        $this->assertSame(0, $exception->getStatusCode());
        $this->assertNull($exception->getResponseBody());
    }
}
