<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain;

use Hobbii\Emarsys\Domain\ContactListsClient;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\HttpClient;
use Hobbii\Emarsys\DTO\ContactList;
use Hobbii\Emarsys\DTO\ContactListCollection;
use Hobbii\Emarsys\DTO\CreateContactListRequest;
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
            'data' => [
                'id' => 1,
                'name' => 'Test List',
                'description' => 'A test list',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('/contactlist', $request->toArray())
            ->willReturn($responseData);

        $result = $this->client->create($request);

        $this->assertInstanceOf(ContactList::class, $result);
        $this->assertSame(1, $result->id);
    }

    public function test_create_contact_list_throws_exception_on_invalid_response(): void
    {
        $request = new CreateContactListRequest('Test List');
        $responseData = ['invalid' => 'response'];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn($responseData);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid response format: missing data field');

        $this->client->create($request);
    }

    public function test_list_contact_lists(): void
    {
        $responseData = [
            'data' => [
                ['id' => 1, 'name' => 'List 1'],
                ['id' => 2, 'name' => 'List 2'],
            ],
            'meta' => ['total' => 2],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with('/contactlist', [])
            ->willReturn($responseData);

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
        $filters = ['limit' => 10, 'offset' => 0];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with('/contactlist', $filters)
            ->willReturn(['data' => []]);

        $result = $this->client->list($filters);

        $this->assertInstanceOf(ContactListCollection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    public function test_delete_contact_list(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('delete')
            ->with('/contactlist/1')
            ->willReturn([]);

        $result = $this->client->delete(1);

        $this->assertTrue($result);
    }
}
