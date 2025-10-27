<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\ContactLists\ValueObjects;

use Hobbii\Emarsys\Domain\ContactLists\ValueObjects\ContactList;
use Hobbii\Emarsys\Domain\ContactLists\ValueObjects\ContactListCollection;
use PHPUnit\Framework\TestCase;

class ContactListCollectionTest extends TestCase
{
    public function test_can_be_created_empty(): void
    {
        $collection = new ContactListCollection([]);

        $this->assertSame(0, $collection->count());
        $this->assertTrue($collection->isEmpty());
    }

    public function test_can_be_created_with_contact_lists(): void
    {
        $contactList1 = new ContactList(1, 'List 1');
        $contactList2 = new ContactList(2, 'List 2');

        $collection = new ContactListCollection([$contactList1, $contactList2]);

        $this->assertSame(2, $collection->count());
        $this->assertFalse($collection->isEmpty());
    }

    public function test_can_be_created_from_array(): void
    {
        $data = [
            [
                'id' => 1,
                'name' => 'List 1',
            ],
            [
                'id' => 2,
                'name' => 'List 2',
            ],
        ];

        $collection = ContactListCollection::from($data);

        $this->assertSame(2, $collection->count());
        $this->assertFalse($collection->isEmpty());

        $this->assertSame(1, $collection[0]->id);
        $this->assertSame('List 1', $collection[0]->name);
        $this->assertSame(2, $collection[1]->id);
        $this->assertSame('List 2', $collection[1]->name);
    }

    public function test_can_be_created_from_empty_array(): void
    {
        $collection = ContactListCollection::from([]);

        $this->assertTrue($collection->isEmpty());
    }

    public function test_throws_exception_for_invalid_items(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All items must be instances of ContactList');

        // @phpstan-ignore-next-line
        new ContactListCollection([new ContactList(1, 'List 1'), new \stdClass]);
    }
}
