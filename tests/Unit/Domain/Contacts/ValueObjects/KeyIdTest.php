<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\Contacts\ValueObjects;

use Hobbii\Emarsys\Domain\Contacts\ValueObjects\KeyId;
use Hobbii\Emarsys\Domain\Enums\ContactSystemField;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Hobbii\Emarsys\Domain\Contacts\ValueObjects\KeyId
 */
final class KeyIdTest extends TestCase
{
    public static function provideKeyIdValues(): array
    {
        return [
            'integer' => [42],
            'zero' => [0],
            'string' => ['email'],
            'enum' => [ContactSystemField::first_name],
        ];
    }

    #[DataProvider('provideKeyIdValues')]
    public function test_make_creates_key_id(mixed $value): void
    {
        $keyId = KeyId::make($value);

        $this->assertInstanceOf(KeyId::class, $keyId);
        $this->assertSame($value instanceof \BackedEnum ? $value->value : $value, $keyId->value);
        $this->assertSame($keyId->value, $keyId->jsonSerialize());
    }

    public static function provideInvalidKeyIdValues(): array
    {
        return [
            'negative integer' => [-1],
            'large negative integer' => [PHP_INT_MIN],
            'empty string' => [''],
        ];
    }

    #[DataProvider('provideInvalidKeyIdValues')]
    public function test_throws_exception_for_invalid_key_id_values(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Key ID cannot be');

        KeyId::make($value);
    }
}
