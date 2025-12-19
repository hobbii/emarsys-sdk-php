<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\ValueObjects;

use BackedEnum;
use InvalidArgumentException;
use JsonSerializable;

final readonly class KeyId implements JsonSerializable
{
    private function __construct(
        public int|string $value,
    ) {}

    public function jsonSerialize(): int|string
    {
        return $this->value;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function make(int|string|BackedEnum $value): self
    {
        if ($value instanceof BackedEnum) {
            return self::make($value->value);
        }

        if (is_int($value) && $value < 0) {
            throw new InvalidArgumentException('Key ID cannot be a negative integer.');
        }

        if (is_string($value) && $value === '') {
            throw new InvalidArgumentException('Key ID cannot be an empty string.');
        }

        return new self($value);
    }
}
