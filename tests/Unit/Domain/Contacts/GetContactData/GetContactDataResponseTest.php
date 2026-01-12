<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\Contacts\GetContactData;

use Hobbii\Emarsys\Domain\Contacts\GetContactData\GetContactDataResponse;
use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
use Hobbii\Emarsys\Domain\ValueObjects\ErrorObject;
use Hobbii\Emarsys\Domain\ValueObjects\Reply;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetContactDataResponse::class)]
final class GetContactDataResponseTest extends TestCase
{
    #[DataProvider('responseScenarioProvider')]
    public function test_from_response_handles_various_scenarios(
        array $responseData,
        int $replyCode,
        string $replyMessage,
        bool $shouldHaveResult,
        int $expectedResultCount,
        bool $shouldHaveErrors,
        int $expectedErrorCount,
        ?string $expectedFirstContactEmail
    ): void {
        $response = $this->createResponse($responseData, $replyCode, $replyMessage);
        $getContactResponse = GetContactDataResponse::fromResponse($response);

        // Test response structure
        $this->assertInstanceOf(GetContactDataResponse::class, $getContactResponse);
        $this->assertSame($shouldHaveResult, $getContactResponse->hasResult());
        $this->assertCount($expectedResultCount, $getContactResponse->result);
        $this->assertSame($shouldHaveErrors, $getContactResponse->hasErrors());
        $this->assertCount($expectedErrorCount, $getContactResponse->errors);

        if ($expectedResultCount > 0) {
            $this->assertContainsOnlyInstancesOf(ContactData::class, $getContactResponse->result);
        }
        if ($expectedErrorCount > 0) {
            $this->assertContainsOnlyInstancesOf(ErrorObject::class, $getContactResponse->errors);
        }

        // Test first contact behavior
        $firstContact = $getContactResponse->getFirstContactData();
        if ($expectedFirstContactEmail) {
            $this->assertInstanceOf(ContactData::class, $firstContact);
            $this->assertSame($expectedFirstContactEmail, $firstContact->getEmail());
        } else {
            $this->assertNull($firstContact);
        }

        // Test reply integration
        $this->assertSame($replyCode, $getContactResponse->replyCode());
        $this->assertSame($replyMessage, $getContactResponse->replyMessage());
    }

    public static function responseScenarioProvider(): array
    {
        $successContacts = [
            ['id' => '123', 'uid' => 'contact-uid-123', '1' => 'John', '2' => 'Doe', '3' => 'john@example.com'],
            ['id' => '124', 'uid' => 'contact-uid-124', '1' => 'Jane', '2' => 'Smith', '3' => 'jane@example.com'],
        ];

        $singleContact = [
            ['id' => '123', 'uid' => 'contact-uid-123', '3' => 'first@example.com'],
        ];

        $errors = [
            ['key' => 'invalid_field', 'errorCode' => 2008, 'errorMsg' => 'No field specified'],
            ['key' => 'value_error', 'errorCode' => 1002, 'errorMsg' => 'Invalid value'],
        ];

        return [
            'success with multiple contacts' => [
                ['result' => $successContacts, 'errors' => []],
                0, 'OK',
                true, 2, false, 0,
                'john@example.com',
            ],
            'error response' => [
                ['result' => [], 'errors' => $errors],
                2008, 'No field specified',
                false, 0, true, 2,
                null,
            ],
            'empty result' => [
                ['result' => [], 'errors' => []],
                0, 'OK',
                false, 0, false, 0,
                null,
            ],
            'single contact' => [
                ['result' => $singleContact, 'errors' => []],
                0, 'OK',
                true, 1, false, 0,
                'first@example.com',
            ],
        ];
    }

    #[DataProvider('edgeCaseProvider')]
    public function test_from_response_handles_edge_cases(array $responseData): void
    {
        $response = $this->createResponse($responseData);
        $getContactResponse = GetContactDataResponse::fromResponse($response);

        $this->assertInstanceOf(GetContactDataResponse::class, $getContactResponse);
        // Edge cases should not have errors and handle missing/null fields gracefully
        $this->assertSame([], $getContactResponse->errors);
    }

    public static function edgeCaseProvider(): array
    {
        $contactData = [['id' => '123', 'uid' => 'contact-uid-123', '3' => 'test@example.com']];

        return [
            'missing result field' => [['errors' => []]],
            'null result field' => [['result' => null, 'errors' => []]],
            'missing errors field' => [['result' => $contactData]],
            'null errors field' => [['result' => $contactData, 'errors' => null]],
        ];
    }

    public static function invalidContactDataProvider(): array
    {
        return [
            'Result data is "false"' => [false, [], 'No contact found. Possible reasons:'],
            'Result data is invalid type "true"' => [true, [], 'Invalid "result" in data response'],
            'Result data is invalid type "string"' => ['invalid', [], 'Invalid "result" in data response'],
            'Result data is invalid type "int"' => [123, [], 'Invalid "result" in data response'],
            'Errors data is invalid type "string"' => [[['id' => '123']], 'invalid', 'Invalid "errors" in data response'],
            'Errors data is invalid type "int"' => [[['id' => '123']], 123, 'Invalid "errors" in data response'],
            'Errors data is invalid type "bool"' => [[['id' => '123']], true, 'Invalid "errors" in data response'],
        ];
    }

    #[DataProvider('invalidContactDataProvider')]
    public function test_from_response_throws_exception(mixed $responseData, mixed $errorsData, string $expectedExceptionMessage): void
    {
        $response = $this->createResponse(['result' => $responseData, 'errors' => $errorsData]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        GetContactDataResponse::fromResponse($response);
    }

    private function createResponse(array $data, int $replyCode = 0, string $replyMessage = 'OK'): Response
    {
        return new Response(
            reply: new Reply($replyCode, $replyMessage),
            data: $data
        );
    }
}
