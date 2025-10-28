<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Authentication;

use DateTimeImmutable;

/**
 * OAuth 2.0 Bearer Token Authentication for Basecamp API
 */
final readonly class OAuth2Authentication implements AuthenticationInterface
{
    public function __construct(
        private string $accessToken,
        private ?DateTimeImmutable $expiresAt = null,
    ) {
    }

    public function getHeaders(): array
    {
        return [
            'Authorization' => sprintf('Bearer %s', $this->accessToken),
        ];
    }

    public function isValid(): bool
    {
        if ($this->expiresAt === null) {
            return true;
        }

        return $this->expiresAt > new DateTimeImmutable();
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
