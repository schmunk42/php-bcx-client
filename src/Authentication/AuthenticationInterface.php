<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Authentication;

/**
 * Interface for authentication strategies with Basecamp API
 */
interface AuthenticationInterface
{
    /**
     * Get authentication headers to be added to requests
     *
     * @return array<string, string>
     */
    public function getHeaders(): array;

    /**
     * Check if authentication is valid
     */
    public function isValid(): bool;
}
