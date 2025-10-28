<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Authentication;

/**
 * HTTP Basic Authentication
 *
 * Simple username/password authentication for Basecamp 2 API.
 * Easier for debugging and development, but OAuth 2.0 is recommended for production.
 *
 * @see https://github.com/basecamp/bcx-api/blob/master/sections/authentication.md#http-basic-authentication
 */
final class BasicAuthentication implements AuthenticationInterface
{
    public function __construct(
        private readonly string $username,
        private readonly string $password,
    ) {
    }

    public function isValid(): bool
    {
        // Basic auth credentials don't expire
        return true;
    }

    public function getHeaders(): array
    {
        $credentials = base64_encode($this->username . ':' . $this->password);

        return [
            'Authorization' => 'Basic ' . $credentials,
        ];
    }
}
