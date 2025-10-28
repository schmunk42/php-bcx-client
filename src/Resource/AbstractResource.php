<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Resource;

use Schmunk42\BasecampApi\Client\BasecampClient;

/**
 * Base class for all API resource clients
 */
abstract class AbstractResource
{
    public function __construct(
        protected readonly BasecampClient $client,
    ) {
    }
}
