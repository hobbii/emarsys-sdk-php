<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\Contacts;

use Hobbii\Emarsys\Domain\BaseClient as EmarsysClient;
use Hobbii\Emarsys\Domain\Contacts\ContactsClient;
use Hobbii\Emarsys\Domain\Contacts\GetContactData\GetContactDataRequest;
use Hobbii\Emarsys\Domain\Contacts\GetContactData\GetContactDataResponse;
use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
use Hobbii\Emarsys\Domain\ValueObjects\Reply;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Hobbii\Emarsys\Domain\Contacts\ContactsClient
 */
final class ContactsClientTest extends TestCase
{
    private ContactsClient $client;

    private EmarsysClient&MockObject $emarsysClient;

    protected function setUp(): void
    {
        $this->emarsysClient = $this->createMock(EmarsysClient::class);
        $this->client = new ContactsClient($this->emarsysClient);
    }

    public function test_get_data_returns_contact_data_on_successful_response(): void
    {
        $responseData = [
            'errors' => [],
            'result' => [
                [
                    'id' => '123',
                    'uid' => 'contact-uid-123',
                    '1' => 'john@example.com',
                    '2' => 'John',
                    '3' => 'Doe',
                ],
            ],
        ];

        $response = new Response(
            reply: new Reply(0, 'OK'),
            data: $responseData
        );

        $requestData = GetContactDataRequest::make(
            fields: ['1', '2', '3'],
            keyId: '1',
            keyValues: ['john@example.com']
        );

        $this->emarsysClient
            ->expects($this->once())
            ->method('send')
            ->with($requestData)
            ->willReturn($response);

        $response = $this->client->getContactData($requestData);

        $this->assertInstanceOf(GetContactDataResponse::class, $response);
        $this->assertFalse($response->hasErrors());
        $this->assertEmpty($response->errors);
        $this->assertTrue($response->hasResult());
        $this->assertNotNull($response->result);
        $this->assertCount(1, $response->result);
        $this->assertInstanceOf(ContactData::class, $response->getFirstContactData());

        // Test WithReply trait methods
        $this->assertSame(0, $response->replyCode());
        $this->assertSame('OK', $response->replyMessage());
    }

    public function test_get_data_returns_empty_result_when_result_field_missing(): void
    {
        $response = new Response(
            reply: new Reply(0, 'OK'),
            data: ['errors' => []] // Missing 'result' field
        );

        $requestData = GetContactDataRequest::make(
            fields: ['1', '2', '3'],
            keyId: '1',
            keyValues: ['john@example.com']
        );

        $this->emarsysClient
            ->expects($this->once())
            ->method('send')
            ->with($requestData)
            ->willReturn($response);

        $responseData = $this->client->getContactData($requestData);

        $this->assertInstanceOf(GetContactDataResponse::class, $responseData);
        $this->assertFalse($responseData->hasResult());
        $this->assertNull($responseData->result);
        $this->assertNull($responseData->getFirstContactData());
        $this->assertFalse($responseData->hasErrors());
    }

    public function test_get_data_handles_errors_correctly(): void
    {
        $responseData = [
            'errors' => [
                [
                    'key' => 'invalid_field',
                    'errorCode' => 2008,
                    'errorMsg' => 'No field specified',
                ],
            ],
            'result' => [],
        ];

        $response = new Response(
            reply: new Reply(2008, 'No field specified'),
            data: $responseData
        );

        $requestData = GetContactDataRequest::make(
            fields: ['1', '2', '3'],
            keyId: '1',
            keyValues: ['john@example.com']
        );

        $this->emarsysClient
            ->expects($this->once())
            ->method('send')
            ->with($requestData)
            ->willReturn($response);

        $responseData = $this->client->getContactData($requestData);

        $this->assertInstanceOf(GetContactDataResponse::class, $responseData);
        $this->assertTrue($responseData->hasErrors());
        $this->assertNotEmpty($responseData->errors);
        $this->assertFalse($responseData->hasResult());
        $this->assertNull($responseData->getFirstContactData());

        // Test WithReply trait methods with error response
        $this->assertSame(2008, $responseData->replyCode());
        $this->assertSame('No field specified', $responseData->replyMessage());
    }
}
