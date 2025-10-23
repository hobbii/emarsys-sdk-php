<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\ContactLists;

use Hobbii\Emarsys\Domain\Client as EmarsysClient;
use Hobbii\Emarsys\Domain\ContactLists\ContactListsClient;
use Hobbii\Emarsys\Domain\ContactLists\DTOs\CreateContactList;
use Hobbii\Emarsys\Domain\ContactLists\ValueObjects\ContactListCollection;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContactListsClientTest extends TestCase
{
    private ContactListsClient $client;

    private EmarsysClient&MockObject $emarsysClient;

    protected function setUp(): void
    {
        $this->emarsysClient = $this->createMock(EmarsysClient::class);
        $this->client = new ContactListsClient($this->emarsysClient);
    }

    public function test_create_contact_list(): void
    {
        $request = new CreateContactList(
            name: 'Test List',
            description: 'A test list'
        );

        $responseData = [
            'id' => 1,
            'name' => 'Test List',
            'description' => 'A test list',
        ];

        $response = new Response(
            replyCode: 0,
            replyText: 'OK',
            data: $responseData,
            errors: []
        );

        $this->emarsysClient
            ->expects($this->once())
            ->method('post')
            ->with('contactlist', $request->toArray())
            ->willReturn($response);

        $contactListId = $this->client->create($request);

        $this->assertSame(1, $contactListId);
    }

    public function test_create_contact_list_throws_exception_on_invalid_response(): void
    {
        $request = new CreateContactList(
            name: 'Test List',
            description: 'A test list'
        );

        $response = new Response(
            replyCode: 0,
            replyText: 'OK',
            data: null,
            errors: []
        );

        $this->emarsysClient
            ->expects($this->once())
            ->method('post')
            ->willReturn($response);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid response format: missing data field');

        $this->client->create($request);
    }

    public function test_list_contact_lists(): void
    {
        $responseData = [
            ['id' => 1, 'name' => 'List 1'],
            ['id' => 2, 'name' => 'List 2'],
        ];

        $response = new Response(
            replyCode: 0,
            replyText: 'OK',
            data: $responseData,
            errors: []
        );

        $this->emarsysClient
            ->expects($this->once())
            ->method('get')
            ->with('contactlist', [])
            ->willReturn($response);

        $result = $this->client->list();

        $this->assertInstanceOf(ContactListCollection::class, $result);
        $this->assertSame(2, $result->count());

        $contactLists = $result->items;
        $this->assertSame(1, $contactLists[0]->id);
        $this->assertSame('List 1', $contactLists[0]->name);
        $this->assertSame(2, $contactLists[1]->id);
        $this->assertSame('List 2', $contactLists[1]->name);
    }

    public function test_delete_contact_list(): void
    {
        $response = new Response(
            replyCode: 0,
            replyText: 'OK',
            data: null,
            errors: []
        );

        $this->emarsysClient
            ->expects($this->once())
            ->method('delete')
            ->with('contactlist/1/deletelist')
            ->willReturn($response);

        $result = $this->client->delete(1);

        $this->assertTrue($result);
    }
}
