<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\Contacts\ValueObjects;

use ArrayIterator;
use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
use Hobbii\Emarsys\Domain\Enums\ContactSystemField;
use Hobbii\Emarsys\Domain\Enums\OptInStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactData::class)]
final class ContactDataTest extends TestCase
{
    public function test_constructor_creates_contact_data_with_array(): void
    {
        $data = [1 => 'John', 2 => 'Doe', 3 => 'john@example.com'];
        $contactData = new ContactData($data);

        $this->assertSame($data, $contactData->data);
    }

    #[DataProvider('hasAndGetDataProvider')]
    public function test_has_and_get_methods(array $data, mixed $existingKey, mixed $nonExistingKey, mixed $expectedValue): void
    {
        $contactData = new ContactData($data);

        // Test has() method
        $this->assertTrue($contactData->has($existingKey));
        $this->assertFalse($contactData->has($nonExistingKey));

        // Test get() method
        $this->assertSame($expectedValue, $contactData->get($existingKey));
        $this->assertNull($contactData->get($nonExistingKey));
    }

    public static function hasAndGetDataProvider(): array
    {
        return [
            'integer keys' => [
                [1 => 'John', 2 => 'Doe'],
                1,
                3,
                'John',
            ],
            'string keys' => [
                ['email' => 'test@example.com', 'name' => 'Test'],
                'email',
                'phone',
                'test@example.com',
            ],
            'enum keys' => [
                [ContactSystemField::first_name->value => 'John', ContactSystemField::email->value => 'john@example.com'],
                ContactSystemField::first_name,
                ContactSystemField::last_name,
                'John',
            ],
            'array values' => [
                [10 => ['option1', 'option2', null]],
                10,
                11,
                ['option1', 'option2', null],
            ],
            'null values' => [
                [1 => null, 2 => 'John'],
                1,
                3,
                null,
            ],
        ];
    }

    #[DataProvider('validOptInStatusProvider')]
    public function test_get_opt_in_status_returns_valid_enums(string $value, OptInStatus $expectedStatus, bool $expectedBool): void
    {
        $contactData = new ContactData([ContactSystemField::optin->value => $value]);

        $status = $contactData->getOptInStatus();
        $this->assertInstanceOf(OptInStatus::class, $status);
        $this->assertSame($expectedStatus, $status);
        $this->assertSame($expectedBool, $status->asBool());
    }

    public static function validOptInStatusProvider(): array
    {
        return [
            'opt-in true' => ['1', OptInStatus::True, true],
            'opt-in false' => ['2', OptInStatus::False, false],
        ];
    }

    public function test_get_opt_in_status_returns_null_for_missing_field(): void
    {
        $contactData = new ContactData([1 => 'John']);
        $this->assertNull($contactData->getOptInStatus());
    }

    #[DataProvider('invalidOptInStatusProvider')]
    public function test_get_opt_in_status_throws_exception_for_invalid_values(mixed $value, string $expectedMessage): void
    {
        $contactData = new ContactData([ContactSystemField::optin->value => $value]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $contactData->getOptInStatus();
    }

    public static function invalidOptInStatusProvider(): array
    {
        return [
            'non-numeric string' => ['invalid', 'Opt-in status must be numeric, got string'],
            'array value' => [['invalid'], 'Opt-in status must be numeric, got array'],
            'invalid numeric' => ['999', 'Invalid opt-in status value: 999'],
        ];
    }

    #[DataProvider('getterMethodsProvider')]
    public function test_getter_methods(array $data, string $method, mixed $expectedValue): void
    {
        $contactData = new ContactData($data);
        $this->assertSame($expectedValue, $contactData->$method());
    }

    public static function getterMethodsProvider(): array
    {
        return [
            // ID tests
            'valid string ID' => [['id' => '123'], 'getId', 123],
            'valid numeric ID' => [['id' => '456'], 'getId', 456],
            'missing ID' => [[1 => 'John'], 'getId', null],
            'invalid ID' => [['id' => 'invalid'], 'getId', null],

            // UID tests
            'valid UID' => [['uid' => 'contact-uid-123'], 'getUid', 'contact-uid-123'],
            'missing UID' => [[1 => 'John'], 'getUid', null],
            'invalid UID' => [['uid' => ['not_a_string']], 'getUid', null],

            // Email tests
            'valid email' => [[ContactSystemField::email->value => 'john@example.com'], 'getEmail', 'john@example.com'],
            'missing email' => [[1 => 'John'], 'getEmail', null],
            'invalid email' => [[ContactSystemField::email->value => ['invalid_email']], 'getEmail', null],

            // First name tests
            'valid first name' => [[ContactSystemField::first_name->value => 'John'], 'getFirstName', 'John'],
            'missing first name' => [[2 => 'Doe'], 'getFirstName', null],
            'invalid first name' => [[ContactSystemField::first_name->value => ['invalid_name']], 'getFirstName', null],

            // Last name tests
            'valid last name' => [[ContactSystemField::last_name->value => 'Doe'], 'getLastName', 'Doe'],
            'missing last name' => [[1 => 'John'], 'getLastName', null],
            'invalid last name' => [[ContactSystemField::last_name->value => ['invalid_name']], 'getLastName', null],
        ];
    }

    public function test_json_serialize_and_iterator_functionality(): void
    {
        $data = [1 => 'John', 2 => 'Doe', 3 => 'john@example.com'];
        $contactData = new ContactData($data);

        // Test JSON serialization
        $this->assertSame($data, $contactData->jsonSerialize());

        // Test iterator functionality
        $iterator = $contactData->getIterator();
        $this->assertInstanceOf(ArrayIterator::class, $iterator);

        $iteratedData = [];
        foreach ($contactData as $key => $value) {
            $iteratedData[$key] = $value;
        }
        $this->assertSame($data, $iteratedData);
    }

    public function test_from_response_result_item_creates_valid_contact_data(): void
    {
        $item = [
            'id' => '123',
            'uid' => 'contact-uid-123',
            '1' => 'John',
            '2' => 'Doe',
            '3' => 'john@example.com',
        ];

        $contactData = ContactData::fromResponseResultItem($item);

        $this->assertInstanceOf(ContactData::class, $contactData);
        $this->assertSame(123, $contactData->getId());
        $this->assertSame('contact-uid-123', $contactData->getUid());
        $this->assertSame('John', $contactData->get(1));
        $this->assertSame('Doe', $contactData->get(2));
        $this->assertSame('john@example.com', $contactData->get(3));
    }

    #[DataProvider('invalidResponseItemProvider')]
    public function test_from_response_result_item_throws_exception_for_invalid_data(array $item, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        ContactData::fromResponseResultItem($item);
    }

    public static function invalidResponseItemProvider(): array
    {
        return [
            'missing ID' => [
                ['uid' => 'contact-uid-123', '3' => 'john@example.com'],
                'Contact data must have a valid numeric id',
            ],
            'invalid ID' => [
                ['id' => 'invalid', 'uid' => 'contact-uid-123'],
                'Contact data must have a valid numeric id',
            ],
            'missing UID' => [
                ['id' => '123', '3' => 'john@example.com'],
                'Contact data must have a valid string uid',
            ],
            'invalid UID' => [
                ['id' => '123', 'uid' => ['not_a_string']],
                'Contact data must have a valid string uid',
            ],
        ];
    }

    public function test_contact_data_handles_empty_array(): void
    {
        $contactData = new ContactData([]);

        $this->assertFalse($contactData->has(1));
        $this->assertNull($contactData->get(1));
        $this->assertNull($contactData->getId());
        $this->assertNull($contactData->getUid());
        $this->assertNull($contactData->getEmail());
        $this->assertNull($contactData->getOptInStatus());
    }
}
