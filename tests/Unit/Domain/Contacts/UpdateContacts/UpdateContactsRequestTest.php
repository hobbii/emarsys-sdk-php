<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\Contacts\UpdateContacts;

use Hobbii\Emarsys\Domain\Contacts\UpdateContacts\UpdateContactsRequest;
use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
use Hobbii\Emarsys\Domain\Contacts\ValueObjects\KeyId;
use Hobbii\Emarsys\Domain\Enums\ContactSystemField;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UpdateContactsRequest::class)]
final class UpdateContactsRequestTest extends TestCase
{
    public function test_make_creates_request_with_key_id_integration(): void
    {
        $contacts = [new ContactData([3 => 'test@example.com'])];

        $request = UpdateContactsRequest::make(keyId: 'email', contacts: $contacts);

        // Test that KeyId is properly created and integrated
        $this->assertInstanceOf(KeyId::class, $request->keyId);
        $this->assertSame('email', $request->keyId->value);
        $this->assertCount(1, $request->contacts);
        $this->assertContainsOnlyInstancesOf(ContactData::class, $request->contacts);
        $this->assertFalse($request->createIfNotExists); // default value
    }

    #[DataProvider('createIfNotExistsProvider')]
    public function test_create_if_not_exists_behavior(bool $createIfNotExists, array $expectedQuery): void
    {
        $contacts = [new ContactData([3 => 'test@example.com'])];

        $request = UpdateContactsRequest::make(
            keyId: 3,
            contacts: $contacts,
            createIfNotExists: $createIfNotExists
        );

        $this->assertSame($createIfNotExists, $request->createIfNotExists);
        $this->assertSame($expectedQuery, $request->query());
    }

    public static function createIfNotExistsProvider(): array
    {
        return [
            'create if not exists true' => [true, ['create_if_not_exists' => '1']],
            'create if not exists false' => [false, []],
        ];
    }

    public function test_http_interface_methods(): void
    {
        $contacts = [new ContactData([3 => 'test@example.com'])];
        $request = UpdateContactsRequest::make(keyId: 3, contacts: $contacts);

        $this->assertSame('PUT', $request->method());
        $this->assertSame('contact/', $request->endpoint());
    }

    public function test_json_serialize_returns_correct_structure(): void
    {
        $contacts = [new ContactData([3 => 'john@example.com', 1 => 'John'])];
        $request = UpdateContactsRequest::make(keyId: ContactSystemField::email, contacts: $contacts);

        $serialized = $request->jsonSerialize();

        $this->assertArrayHasKey('keyId', $serialized);
        $this->assertArrayHasKey('contacts', $serialized);
        $this->assertInstanceOf(KeyId::class, $serialized['keyId']);
        $this->assertSame((string) ContactSystemField::email->value, $serialized['keyId']->value);
        $this->assertCount(1, $serialized['contacts']);
        $this->assertContainsOnlyInstancesOf(ContactData::class, $serialized['contacts']);
    }

    #[DataProvider('validContactCountProvider')]
    public function test_make_accepts_valid_contact_counts(int $contactCount): void
    {
        $contacts = [];
        for ($i = 0; $i < $contactCount; $i++) {
            $contacts[] = new ContactData([3 => "contact{$i}@example.com"]);
        }

        $request = UpdateContactsRequest::make(keyId: 3, contacts: $contacts);

        $this->assertCount($contactCount, $request->contacts);
    }

    public static function validContactCountProvider(): array
    {
        return [
            'single contact' => [1],
            'multiple contacts' => [5],
            'exactly 1000 contacts' => [1000],
        ];
    }

    #[DataProvider('invalidContactsProvider')]
    public function test_make_throws_exception_for_invalid_contacts(array $contacts, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        UpdateContactsRequest::make(keyId: 3, contacts: $contacts);
    }

    public static function invalidContactsProvider(): array
    {
        $tooManyContacts = [];
        for ($i = 0; $i < 1001; $i++) {
            $tooManyContacts[] = new ContactData([3 => "contact{$i}@example.com"]);
        }

        return [
            'empty contacts' => [[], 'Contacts array cannot be empty'],
            'too many contacts' => [$tooManyContacts, 'The maximum batch size is 1000 contacts per call. You provided 1001 items.'],
        ];
    }

    public function test_make_handles_complex_contact_data(): void
    {
        $contacts = [
            new ContactData([
                ContactSystemField::email->value => 'john@example.com',
                ContactSystemField::first_name->value => 'John',
                ContactSystemField::last_name->value => 'Doe',
                ContactSystemField::optin->value => '1',
                ContactSystemField::phone->value => '+1234567890',
            ]),
            new ContactData([
                ContactSystemField::email->value => 'jane@example.com',
                ContactSystemField::first_name->value => 'Jane',
                ContactSystemField::last_name->value => 'Smith',
                ContactSystemField::optin->value => '2',
            ]),
        ];

        $request = UpdateContactsRequest::make(
            keyId: ContactSystemField::email,
            contacts: $contacts,
            createIfNotExists: true
        );

        $this->assertCount(2, $request->contacts);
        $this->assertTrue($request->createIfNotExists);
        $this->assertSame((string) ContactSystemField::email->value, $request->keyId->value);

        // Verify contacts maintain their data
        $firstContact = $request->contacts[0];
        $this->assertSame('john@example.com', $firstContact->get(ContactSystemField::email));
        $this->assertSame('John', $firstContact->get(ContactSystemField::first_name));
        $this->assertSame('+1234567890', $firstContact->get(ContactSystemField::phone));

        $secondContact = $request->contacts[1];
        $this->assertSame('jane@example.com', $secondContact->get(ContactSystemField::email));
        $this->assertSame('Jane', $secondContact->get(ContactSystemField::first_name));
        $this->assertNull($secondContact->get(ContactSystemField::phone));
    }
}
