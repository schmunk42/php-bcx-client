<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Tests\Unit\Authentication;

use PHPUnit\Framework\TestCase;
use Schmunk42\BasecampApi\Authentication\BasicAuthentication;

final class BasicAuthenticationTest extends TestCase
{
    public function testGetHeadersReturnsBasicAuthorizationHeader(): void
    {
        $auth = new BasicAuthentication('john@example.com', 'secret-password');
        $headers = $auth->getHeaders();

        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertStringStartsWith('Basic ', $headers['Authorization']);
    }

    public function testGetHeadersEncodesCredentialsCorrectly(): void
    {
        $auth = new BasicAuthentication('user@example.com', 'pass123');
        $headers = $auth->getHeaders();

        // Decode and verify
        $encoded = str_replace('Basic ', '', $headers['Authorization']);
        $decoded = base64_decode($encoded);

        $this->assertSame('user@example.com:pass123', $decoded);
    }

    public function testIsValidAlwaysReturnsTrue(): void
    {
        $auth = new BasicAuthentication('user', 'pass');

        $this->assertTrue($auth->isValid());
    }

    public function testWorksWithSpecialCharactersInPassword(): void
    {
        $auth = new BasicAuthentication('user@test.com', 'p@$$w0rd!');
        $headers = $auth->getHeaders();

        $encoded = str_replace('Basic ', '', $headers['Authorization']);
        $decoded = base64_decode($encoded);

        $this->assertSame('user@test.com:p@$$w0rd!', $decoded);
    }
}
