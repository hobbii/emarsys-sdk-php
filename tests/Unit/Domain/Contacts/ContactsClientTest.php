<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\Contacts;

use Hobbii\Emarsys\Domain\BaseClient as EmarsysClient;
use Hobbii\Emarsys\Domain\Contacts\ContactsClient;
use Hobbii\Emarsys\Domain\Contacts\GetContactData\GetContactDataRequest;
use Hobbii\Emarsys\Domain\Contacts\GetContactData\GetContactDataResponseData;
use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
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
        // Arrange
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
            replyCode: 0,
            replyText: 'OK',
            data: $responseData,
            errors: []
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

        // Act
        $response = $this->client->getContactData($requestData);

        // Assert
        $this->assertInstanceOf(GetContactDataResponseData::class, $response);
        $this->assertEmpty($response->errors);
        $this->assertCount(1, $response->contactDataResult);
        $this->assertInstanceOf(ContactData::class, $response->getFirstContactData());
    }

    public function test_get_data_throws_invalid_argument_exception_when_result_field_missing(): void
    {
        // Arrange
        $response = new Response(
            replyCode: 0,
            replyText: 'OK',
            data: ['errors' => []], // Missing 'result' field
            errors: []
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

        // Assert & Act
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing "result" in contact data response');

        $this->client->getContactData($requestData);
    }
}
