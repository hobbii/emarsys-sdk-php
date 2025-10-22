<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit;

use Hobbii\Emarsys\Client;
use Hobbii\Emarsys\Domain\ContactLists\ContactListsClient;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = new Client('test-client-id', 'test-client-secret');
    }

    public function test_can_be_created(): void
    {
        $this->assertInstanceOf(Client::class, $this->client);
    }

    public function test_contact_lists_returns_contact_lists_client(): void
    {
        $contactListsClient = $this->client->contactLists();

        $this->assertInstanceOf(ContactListsClient::class, $contactListsClient);
    }

    public function test_contact_lists_returns_same_instance(): void
    {
        $contactListsClient1 = $this->client->contactLists();
        $contactListsClient2 = $this->client->contactLists();

        $this->assertSame($contactListsClient1, $contactListsClient2);
    }

    public function test_can_be_created_with_custom_base_url(): void
    {
        $client = new Client('test-client-id', 'test-client-secret', 'https://custom.api.url');

        $this->assertInstanceOf(Client::class, $client);
    }
}
