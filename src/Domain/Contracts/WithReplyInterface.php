<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contracts;

interface WithReplyInterface
{
    public function replyCode(): int;

    public function replyMessage(): string;
}
