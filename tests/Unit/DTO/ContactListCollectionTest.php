<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\DTO;

use Hobbii\Emarsys\Domain\DTOs\ContactList;
use Hobbii\Emarsys\Domain\DTOs\ContactListCollection;
use PHPUnit\Framework\TestCase;

class ContactListCollectionTest extends TestCase
{
    public function test_can_be_created_empty(): void
    {
        $collection = new ContactListCollection([]);

        $this->assertEmpty($collection->getContactLists());
        $this->assertSame(0, $collection->count());
        $this->assertTrue($collection->isEmpty());
        $this->assertNull($collection->meta);
    }

    public function test_can_be_created_with_contact_lists(): void
    {
        $contactList1 = new ContactList(1, 'List 1');
        $contactList2 = new ContactList(2, 'List 2');
        $meta = ['total' => 2, 'page' => 1];

        $collection = new ContactListCollection([$contactList1, $contactList2], $meta);

        $this->assertCount(2, $collection->getContactLists());
        $this->assertSame(2, $collection->count());
        $this->assertFalse($collection->isEmpty());
        $this->assertSame($meta, $collection->meta);
    }

    public function test_can_be_created_from_array(): void
    {
        $data = [
            'data' => [
                [
                    'id' => 1,
                    'name' => 'List 1',
                ],
                [
                    'id' => 2,
                    'name' => 'List 2',
                ],
            ],
            'meta' => [
                'total' => 2,
                'page' => 1,
            ],
        ];

        $collection = ContactListCollection::fromArray($data);

        $this->assertCount(2, $collection->getContactLists());
        $this->assertSame(2, $collection->count());
        $this->assertFalse($collection->isEmpty());

        $contactLists = $collection->getContactLists();
        $this->assertSame(1, $contactLists[0]->id);
        $this->assertSame('List 1', $contactLists[0]->name);
        $this->assertSame(2, $contactLists[1]->id);
        $this->assertSame('List 2', $contactLists[1]->name);

        $this->assertSame(['total' => 2, 'page' => 1], $collection->meta);
    }

    public function test_can_be_created_from_array_without_data(): void
    {
        $data = [
            'meta' => ['total' => 0],
        ];

        $collection = ContactListCollection::fromArray($data);

        $this->assertEmpty($collection->getContactLists());
        $this->assertTrue($collection->isEmpty());
        $this->assertSame(['total' => 0], $collection->meta);
    }

    public function test_can_be_created_from_empty_array(): void
    {
        $collection = ContactListCollection::fromArray([]);

        $this->assertEmpty($collection->getContactLists());
        $this->assertTrue($collection->isEmpty());
        $this->assertNull($collection->meta);
    }
}
