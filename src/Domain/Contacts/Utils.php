<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts;

use BackedEnum;
use InvalidArgumentException;

final class Utils
{
    /**
     * Normalize key ID from various input types to int or string.
     *
     * Handles BackedEnum values by extracting their underlying value,
     * while passing through int and string values unchanged.
     */
    public static function normalizeKeyId(int|string|BackedEnum $keyId): int|string
    {
        if (is_int($keyId)) {
            return $keyId;
        }

        if ($keyId instanceof BackedEnum) {
            $keyId = $keyId->value;
        }

        return $keyId;
    }

    /**
     * Normalize field ID from various input types to int or string.
     *
     * Handles BackedEnum values by extracting their underlying value,
     * while passing through int values unchanged.
     */
    public static function normalizeFieldId(int|string|BackedEnum $fieldId): int
    {
        if (is_int($fieldId)) {
            return $fieldId;
        }

        if ($fieldId instanceof BackedEnum) {
            if (! is_int($fieldId->value)) {
                throw new InvalidArgumentException('Field enum must have an integer backing value. Got '.gettype($fieldId->value).'.');
            }

            $fieldId = $fieldId->value;
        }

        return (int) $fieldId;
    }
}
