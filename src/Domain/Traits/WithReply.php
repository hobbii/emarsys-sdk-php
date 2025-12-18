<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Traits;

use Hobbii\Emarsys\Domain\ValueObjects\Reply;

trait WithReply
{
    public readonly Reply $reply;

    public function replyCode(): int
    {
        return $this->reply->code;
    }

    public function replyMessage(): string
    {
        return $this->reply->message;
    }
}
