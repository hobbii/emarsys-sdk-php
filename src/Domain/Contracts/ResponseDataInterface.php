<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contracts;

use Hobbii\Emarsys\Domain\ValueObjects\Response;

interface ResponseDataInterface
{
    public static function fromResponse(Response $response): self;
}
