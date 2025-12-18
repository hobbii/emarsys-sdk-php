<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contracts;

use Hobbii\Emarsys\Domain\ValueObjects\Response;

interface ResponseInterface extends WithReplyInterface
{
    public static function fromResponse(Response $response): self;
}
