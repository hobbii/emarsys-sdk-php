<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\ContactLists\DTOs;

use Hobbii\Emarsys\Domain\ContactLists\DTOs\CreateContactList;
use PHPUnit\Framework\TestCase;

class CreateContactListTest extends TestCase
{
    public function test_can_be_created_with_required_fields(): void
    {
        $createData = new CreateContactList(
            name: 'Test List'
        );

        $this->assertSame('Test List', $createData->name);
        $this->assertNull($createData->description);
        $this->assertSame('email', $createData->keyId);
        $this->assertNull($createData->externalIds);
    }

    public function test_can_be_created_with_all_fields(): void
    {
        $createData = new CreateContactList(
            name: 'Test List',
            description: 'A test contact list',
            keyId: '3',
            externalIds: [1, 2]
        );

        $this->assertSame('Test List', $createData->name);
        $this->assertSame('A test contact list', $createData->description);
        $this->assertSame('3', $createData->keyId);
        $this->assertSame([1, 2], $createData->externalIds);
    }

    public function test_can_be_converted_to_array(): void
    {
        $createData = new CreateContactList(
            name: 'Test List',
            description: 'A test contact list',
        );

        $array = $createData->toArray();

        $expected = [
            'name' => 'Test List',
            'description' => 'A test contact list',
            'key_id' => 'email',
        ];

        $this->assertEquals($expected, $array);
    }
}
