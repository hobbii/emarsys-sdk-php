<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\Contacts\ValueObjects;

use Hobbii\Emarsys\Domain\Contacts\ValueObjects\KeyId;
use Hobbii\Emarsys\Domain\Enums\ContactSystemField;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Hobbii\Emarsys\Domain\Contacts\ValueObjects\KeyId
 */
final class KeyIdTest extends TestCase
{
    public static function provideValidValues(): array
    {
        return [
            'positive integer' => [42],
            'zero' => [0],
            'large integer' => [PHP_INT_MAX],
            'email string' => ['email'],
            'custom field' => ['custom_field'],
            'numeric string' => ['123'],
            'enum' => [ContactSystemField::first_name],
        ];
    }

    #[DataProvider('provideValidValues')]
    public function test_make_creates_key_id_with_valid_values(mixed $value): void
    {
        $keyId = KeyId::make($value);

        $this->assertInstanceOf(KeyId::class, $keyId);
        $this->assertSame($value instanceof \BackedEnum ? $value->value : $value, $keyId->value);
        $this->assertSame($keyId->value, $keyId->jsonSerialize());
    }

    public static function provideInvalidValues(): array
    {
        return [
            'negative integer' => [-1, 'Key ID cannot be a negative integer.'],
            'empty string' => ['', 'Key ID cannot be an empty string.'],
        ];
    }

    #[DataProvider('provideInvalidValues')]
    public function test_make_throws_exception_for_invalid_values(mixed $value, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        KeyId::make($value);
    }

    public static function provideInvalidTypes(): array
    {
        return [
            'array' => [[]],
            'object' => [new stdClass],
            'null' => [null],
            'boolean' => [true],
            'float' => [3.14],
        ];
    }

    #[DataProvider('provideInvalidTypes')]
    public function test_make_throws_type_error_for_invalid_types(mixed $value): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessageMatches('/must be of type BackedEnum\|string\|int/');

        KeyId::make($value);
    }
}
