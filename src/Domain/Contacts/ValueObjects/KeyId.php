<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\ValueObjects;

use BackedEnum;
use InvalidArgumentException;
use JsonSerializable;

/**
 * According to the Emarsys documentation:
 * >> Key ID identifies the contact by their `id`, `uid`, or the name/integer id of a custom field, such as `email`.
 *
 * @see https://dev.emarsys.com/docs/core-api-reference/f8ljhut3ac2i1-update-contacts Update Contacts API Endpoint
 * @see https://dev.emarsys.com/docs/core-api-reference/blzojxt3ga5be-get-contact-data Get Contact Data API Endpoint
 */
final readonly class KeyId implements JsonSerializable
{
    private function __construct(
        public string $value,
    ) {}

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromInt(int $value): self
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Key ID cannot be a negative integer.');
        }

        return new self((string) $value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromString(string $value): self
    {
        if ($value === '') {
            throw new InvalidArgumentException('Key ID cannot be an empty string.');
        }

        return new self($value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromBackedEnum(BackedEnum $value): self
    {
        return self::make($value->value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function make(int|string|BackedEnum $value): self
    {
        if (is_int($value)) {
            return self::fromInt($value);
        }

        if (is_string($value)) {
            return self::fromString($value);
        }

        return self::fromBackedEnum($value);
    }
}
