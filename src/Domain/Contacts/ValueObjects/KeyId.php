<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\ValueObjects;

use BackedEnum;
use InvalidArgumentException;
use JsonSerializable;

final readonly class KeyId implements JsonSerializable
{
    public function __construct(
        public int|string $value,
    ) {
        if (is_int($value) && $value < 0) {
            throw new InvalidArgumentException('Key ID cannot be a negative integer.');
        }
    }

    public function jsonSerialize(): int|string
    {
        return $this->value;
    }

    public static function make(int|string|BackedEnum $value): self
    {
        if ($value instanceof BackedEnum) {
            return self::make($value->value);
        }

        return new self($value);
    }
}
