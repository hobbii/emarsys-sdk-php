<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain;

use Hobbii\Emarsys\Domain\ContactListsClient;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\HttpClient;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use Hobbii\Emarsys\Domain\DTOs\ContactListCollection;
use Hobbii\Emarsys\Domain\DTOs\CreateContactListRequest;
use Hobbii\Emarsys\Domain\DTOs\CreateContactListResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContactListsClientTest extends TestCase
{
    private ContactListsClient $client;

    private HttpClient&MockObject $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->client = new ContactListsClient($this->httpClient);
    }

    public function test_create_contact_list(): void
    {
        $request = new CreateContactListRequest(
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

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('contactlist', $request->toArray())
            ->willReturn($response);

        $result = $this->client->create($request);

        $this->assertInstanceOf(CreateContactListResponse::class, $result);
        $this->assertSame(1, $result->id);
    }

    public function test_create_contact_list_throws_exception_on_invalid_response(): void
    {
        $request = new CreateContactListRequest(
            name: 'Test List',
            description: 'A test list'
        );

        $response = new Response(
            replyCode: 0,
            replyText: 'OK',
            data: null,
            errors: []
        );

        $this->httpClient
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

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with('contactlist', [])
            ->willReturn($response);

        $result = $this->client->list();

        $this->assertInstanceOf(ContactListCollection::class, $result);
        $this->assertSame(2, $result->count());

        $contactLists = $result->getContactLists();
        $this->assertSame(1, $contactLists[0]->id);
        $this->assertSame('List 1', $contactLists[0]->name);
        $this->assertSame(2, $contactLists[1]->id);
        $this->assertSame('List 2', $contactLists[1]->name);
    }

    public function test_list_contact_lists_with_filters(): void
    {
        $response = new Response(
            replyCode: 0,
            replyText: 'OK',
            data: [],
            errors: []
        );

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with('contactlist')
            ->willReturn($response);

        $result = $this->client->list();

        $this->assertInstanceOf(ContactListCollection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    public function test_delete_contact_list(): void
    {
        $response = new Response(
            replyCode: 0,
            replyText: 'OK',
            data: null,
            errors: []
        );

        $this->httpClient
            ->expects($this->once())
            ->method('delete')
            ->with('contactlist/1/deletelist')
            ->willReturn($response);

        $result = $this->client->delete(1);

        $this->assertTrue($result);
    }
}
