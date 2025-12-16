<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\Contacts;

use Hobbii\Emarsys\Domain\BaseClient as EmarsysClient;
use Hobbii\Emarsys\Domain\Contacts\ContactsClient;
use Hobbii\Emarsys\Domain\Contacts\GetContactData\GetContactDataRequest;
use Hobbii\Emarsys\Domain\Contacts\GetContactData\GetContactDataResponseData;
use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Hobbii\Emarsys\Domain\Contact\ContactClient
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
            ->method('post')
            ->with('contact/getdata', $requestData)
            ->willReturn($response);

        // Act
        $result = $this->client->getContactData($requestData);

        // Assert
        $this->assertInstanceOf(GetContactDataResponseData::class, $result);
        $this->assertEmpty($result->errors);
        $this->assertNotNull($result->result);
        $this->assertCount(1, $result->result);
        $this->assertInstanceOf(ContactData::class, $result->result[0]);
    }

    public function test_get_data_throws_api_exception_when_data_field_missing(): void
    {
        // Arrange
        $response = new Response(
            replyCode: 0,
            replyText: 'OK',
            data: null,
            errors: []
        );

        $requestData = GetContactDataRequest::make(
            fields: ['1', '2', '3'],
            keyId: '1',
            keyValues: ['john@example.com']
        );

        $this->emarsysClient
            ->expects($this->once())
            ->method('post')
            ->with('contact/getdata', $requestData)
            ->willReturn($response);

        // Assert & Act
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Response data is not an array');

        $this->client->getContactData($requestData);
    }
}
