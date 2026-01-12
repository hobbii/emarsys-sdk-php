<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\Contacts\GetContactData;

use Hobbii\Emarsys\Domain\Contacts\GetContactData\GetContactDataRequest;
use Hobbii\Emarsys\Domain\Contacts\ValueObjects\KeyId;
use Hobbii\Emarsys\Domain\Enums\ContactSystemField;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetContactDataRequest::class)]
final class GetContactDataRequestTest extends TestCase
{
    #[DataProvider('makeRequestProvider')]
    public function test_make_creates_request_with_various_input_types(
        array $fields,
        mixed $keyId,
        array $keyValues,
        int $expectedFieldCount,
        mixed $expectedKeyIdValue,
        array $expectedKeyValues
    ): void {
        $request = GetContactDataRequest::make(
            fields: $fields,
            keyId: $keyId,
            keyValues: $keyValues
        );

        $this->assertCount($expectedFieldCount, $request->fields);
        $this->assertContainsOnlyInstancesOf(KeyId::class, $request->fields);
        $this->assertInstanceOf(KeyId::class, $request->keyId);
        $this->assertSame((string) $expectedKeyIdValue, $request->keyId->value);
        $this->assertSame($expectedKeyValues, $request->keyValues);
    }

    public static function makeRequestProvider(): array
    {
        return [
            'string fields and keyId' => [
                ['1', '2', '3'],
                'email',
                ['john@example.com', 'jane@example.com'],
                3,
                'email',
                ['john@example.com', 'jane@example.com'],
            ],
            'integer fields and keyId' => [
                [1, 2, 3],
                3,
                ['test@example.com'],
                3,
                3,
                ['test@example.com'],
            ],
            'enum fields and keyId' => [
                [ContactSystemField::first_name, ContactSystemField::last_name, ContactSystemField::email],
                ContactSystemField::email,
                ['user@example.com'],
                3,
                ContactSystemField::email->value,
                ['user@example.com'],
            ],
            'mixed field types' => [
                [1, 'email', ContactSystemField::first_name],
                ContactSystemField::email,
                ['mixed@example.com'],
                3,
                ContactSystemField::email->value,
                ['mixed@example.com'],
            ],
            'empty key values' => [
                [ContactSystemField::email],
                ContactSystemField::email,
                [],
                1,
                ContactSystemField::email->value,
                [],
            ],
        ];
    }

    public function test_http_interface_methods(): void
    {
        $request = GetContactDataRequest::make(
            fields: [1],
            keyId: 'email',
            keyValues: ['test@example.com']
        );

        $this->assertSame('POST', $request->method());
        $this->assertSame('contact/getdata/', $request->endpoint());
        $this->assertSame([], $request->query());
    }

    public function test_json_serialize_returns_correct_structure(): void
    {
        $request = GetContactDataRequest::make(
            fields: [ContactSystemField::first_name, ContactSystemField::email],
            keyId: ContactSystemField::email,
            keyValues: ['test@example.com', 'user@example.com']
        );

        $serialized = $request->jsonSerialize();

        $this->assertArrayHasKey('fields', $serialized);
        $this->assertArrayHasKey('keyId', $serialized);
        $this->assertArrayHasKey('keyValues', $serialized);
        $this->assertCount(2, $serialized['fields']);
        $this->assertContainsOnlyInstancesOf(KeyId::class, $serialized['fields']);
        $this->assertInstanceOf(KeyId::class, $serialized['keyId']);
        $this->assertSame(['test@example.com', 'user@example.com'], $serialized['keyValues']);
    }
}
