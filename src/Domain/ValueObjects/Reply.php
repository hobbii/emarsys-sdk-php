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
    public static function fromResponseData(array $data): self
    {
        if (! isset($data['replyCode']) || ! is_int($data['replyCode'])) {
            throw new InvalidArgumentException('Invalid response structure: missing replyCode');
        }

        if (! isset($data['replyText']) || ! is_string($data['replyText']) || empty($data['replyText'])) {
            throw new InvalidArgumentException('Invalid response structure: missing replyText');
        }

        return new self(
            code: $data['replyCode'],
            message: $data['replyText']
        );
    }
}
