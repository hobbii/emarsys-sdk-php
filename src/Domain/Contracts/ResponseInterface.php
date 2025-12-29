<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contracts;

use Hobbii\Emarsys\Domain\ValueObjects\Response;

/**
 * Implement this interface to create response objects that can be created from a Response value object.
 */
interface ResponseInterface extends WithReplyInterface
{
    public static function fromResponse(Response $response): self;
}
