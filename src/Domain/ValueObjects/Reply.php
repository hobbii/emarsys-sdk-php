<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Reply
{
    public function __construct(
        public int $code,
        public string $message,
    ) {}

    /**
     * @throws InvalidArgumentException If required fields are missing or invalid
     */
    public static function fromArray(array $data): self
    {
        if (! isset($data['replyCode']) || ! is_int($data['replyCode'])) {
            throw new InvalidArgumentException('Invalid response structure: missing replyCode');
        }

        return new self(
            code: $data['replyCode'] ?? 0,
            message: $data['replyText'] ?? ''
        );
    }
}
