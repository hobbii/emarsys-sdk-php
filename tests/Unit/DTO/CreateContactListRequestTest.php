<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\DTO;

use Hobbii\Emarsys\DTO\CreateContactListRequest;
use PHPUnit\Framework\TestCase;

class CreateContactListRequestTest extends TestCase
{
    public function test_can_be_created_with_only_name(): void
    {
        $request = new CreateContactListRequest('Test List');

        $this->assertSame('Test List', $request->name);
        $this->assertNull($request->description);
        $this->assertNull($request->type);
    }

    public function test_can_be_created_with_all_fields(): void
    {
        $request = new CreateContactListRequest(
            name: 'Test List',
            description: 'A test contact list',
            type: 'static'
        );

        $this->assertSame('Test List', $request->name);
        $this->assertSame('A test contact list', $request->description);
        $this->assertSame('static', $request->type);
    }

    public function test_can_be_converted_to_array(): void
    {
        $request = new CreateContactListRequest(
            name: 'Test List',
            description: 'A test contact list',
            type: 'static'
        );

        $array = $request->toArray();

        $expected = [
            'name' => 'Test List',
            'description' => 'A test contact list',
            'type' => 'static',
        ];

        $this->assertEquals($expected, $array);
    }

    public function test_to_array_filters_null_values(): void
    {
        $request = new CreateContactListRequest('Test List');

        $array = $request->toArray();

        $expected = [
            'name' => 'Test List',
        ];

        $this->assertEquals($expected, $array);
    }
}
