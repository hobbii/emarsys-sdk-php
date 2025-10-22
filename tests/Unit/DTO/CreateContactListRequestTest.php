<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\DTO;

use Hobbii\Emarsys\Domain\DTOs\CreateContactListRequest;
use PHPUnit\Framework\TestCase;

class CreateContactListRequestTest extends TestCase
{
    public function test_can_be_created_with_required_fields(): void
    {
        $request = new CreateContactListRequest(
            name: 'Test List',
            description: 'A test contact list',
        );

        $this->assertSame('Test List', $request->name);
        $this->assertSame('A test contact list', $request->description);
        $this->assertSame('email', $request->keyId);
        $this->assertEmpty($request->externalIds);
    }

    public function test_can_be_created_with_all_fields(): void
    {
        $request = new CreateContactListRequest(
            name: 'Test List',
            description: 'A test contact list',
            keyId: '3',
            externalIds: [1, 2]
        );

        $this->assertSame('Test List', $request->name);
        $this->assertSame('A test contact list', $request->description);
        $this->assertSame('3', $request->keyId);
        $this->assertSame([1, 2], $request->externalIds);
    }

    public function test_can_be_converted_to_array(): void
    {
        $request = new CreateContactListRequest(
            name: 'Test List',
            description: 'A test contact list',
        );

        $array = $request->toArray();

        $expected = [
            'name' => 'Test List',
            'description' => 'A test contact list',
            'key_id' => 'email',
            'external_ids' => [],
        ];

        $this->assertEquals($expected, $array);
    }
}
