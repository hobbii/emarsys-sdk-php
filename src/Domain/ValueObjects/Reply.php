<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ValueObjects;

final class Reply
{
    public function __construct(
        public int $code,
        public string $message,
    ) {}
}
