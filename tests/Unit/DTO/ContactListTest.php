<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\DTO;

use Hobbii\Emarsys\DTO\ContactList;
use PHPUnit\Framework\TestCase;

class ContactListTest extends TestCase
{
    public function test_can_be_created_with_minimal_data(): void
    {
        $contactList = new ContactList(
            id: 1,
            name: 'Test List'
        );

        $this->assertSame(1, $contactList->id);
        $this->assertSame('Test List', $contactList->name);
        $this->assertNull($contactList->description);
        $this->assertNull($contactList->created);
        $this->assertNull($contactList->type);
        $this->assertNull($contactList->count);
    }

    public function test_can_be_created_with_all_data(): void
    {
        $contactList = new ContactList(
            id: 1,
            name: 'Test List',
            description: 'A test contact list',
            created: '2023-01-01T00:00:00Z',
            type: 'static',
            count: 100
        );

        $this->assertSame(1, $contactList->id);
        $this->assertSame('Test List', $contactList->name);
        $this->assertSame('A test contact list', $contactList->description);
        $this->assertSame('2023-01-01T00:00:00Z', $contactList->created);
        $this->assertSame('static', $contactList->type);
        $this->assertSame(100, $contactList->count);
    }

    public function test_can_be_created_from_array(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test List',
            'description' => 'A test contact list',
            'created' => '2023-01-01T00:00:00Z',
            'type' => 'static',
            'count' => 100,
        ];

        $contactList = ContactList::fromArray($data);

        $this->assertSame(1, $contactList->id);
        $this->assertSame('Test List', $contactList->name);
        $this->assertSame('A test contact list', $contactList->description);
        $this->assertSame('2023-01-01T00:00:00Z', $contactList->created);
        $this->assertSame('static', $contactList->type);
        $this->assertSame(100, $contactList->count);
    }

    public function test_can_be_created_from_array_with_missing_optional_fields(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test List',
        ];

        $contactList = ContactList::fromArray($data);

        $this->assertSame(1, $contactList->id);
        $this->assertSame('Test List', $contactList->name);
        $this->assertNull($contactList->description);
    }

    public function test_can_be_converted_to_array(): void
    {
        $contactList = new ContactList(
            id: 1,
            name: 'Test List',
            description: 'A test contact list',
            created: '2023-01-01T00:00:00Z',
            type: 'static',
            count: 100
        );

        $array = $contactList->toArray();

        $expected = [
            'id' => 1,
            'name' => 'Test List',
            'description' => 'A test contact list',
            'created' => '2023-01-01T00:00:00Z',
            'type' => 'static',
            'count' => 100,
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_to_array_filters_null_values(): void
    {
        $contactList = new ContactList(
            id: 1,
            name: 'Test List'
        );

        $array = $contactList->toArray();

        $expected = [
            'id' => 1,
            'name' => 'Test List',
        ];

        $this->assertEquals($expected, $array);
    }
}
