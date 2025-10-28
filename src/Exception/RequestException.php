<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Exception;

/**
 * Exception thrown when HTTP request fails
 */
final class RequestException extends BasecampApiException
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 0,
        private readonly ?string $responseBody = null,
    ) {
        parent::__construct($message, $statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }
}
