<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Authentication;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\OAuth2Authentication;

final class OAuth2AuthenticationTest extends TestCase
{
    public function testGetHeaders(): void
    {
        $auth = new OAuth2Authentication('test-token-123');

        $headers = $auth->getHeaders();

        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertSame('Bearer test-token-123', $headers['Authorization']);
    }

    public function testIsValidWithoutExpiry(): void
    {
        $auth = new OAuth2Authentication('test-token');

        $this->assertTrue($auth->isValid());
    }

    public function testIsValidWithFutureExpiry(): void
    {
        $expiresAt = (new DateTimeImmutable())->modify('+1 hour');
        $auth = new OAuth2Authentication('test-token', $expiresAt);

        $this->assertTrue($auth->isValid());
    }

    public function testIsNotValidWithPastExpiry(): void
    {
        $expiresAt = (new DateTimeImmutable())->modify('-1 hour');
        $auth = new OAuth2Authentication('test-token', $expiresAt);

        $this->assertFalse($auth->isValid());
    }

    public function testGetAccessToken(): void
    {
        $auth = new OAuth2Authentication('my-secret-token');

        $this->assertSame('my-secret-token', $auth->getAccessToken());
    }

    public function testGetExpiresAt(): void
    {
        $expiresAt = new DateTimeImmutable('2025-12-31 23:59:59');
        $auth = new OAuth2Authentication('test-token', $expiresAt);

        $this->assertSame($expiresAt, $auth->getExpiresAt());
    }
}
