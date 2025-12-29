<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\Contacts\UpdateContacts;

use Hobbii\Emarsys\Domain\Contacts\UpdateContacts\UpdateContactsResponse;
use Hobbii\Emarsys\Domain\ValueObjects\ErrorObject;
use Hobbii\Emarsys\Domain\ValueObjects\Reply;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UpdateContactsResponse::class)]
final class UpdateContactsResponseTest extends TestCase
{
    #[DataProvider('validResponseProvider')]
    public function test_from_response_creates_valid_responses(
        array $responseData,
        array $reply,
        array $expectedIds,
        int $expectedErrorCount,
        bool $shouldHaveErrors
    ): void {
        $response = $this->createResponse($responseData, $reply['code'], $reply['message']);
        $updateResponse = UpdateContactsResponse::fromResponse($response);

        $this->assertInstanceOf(UpdateContactsResponse::class, $updateResponse);
        $this->assertSame($expectedIds, $updateResponse->ids);
        $this->assertSame($shouldHaveErrors, $updateResponse->hasErrors());
        $this->assertCount($expectedErrorCount, $updateResponse->errors);

        if ($expectedErrorCount > 0) {
            $this->assertContainsOnlyInstancesOf(ErrorObject::class, $updateResponse->errors);
        }

        // Test reply integration
        $this->assertSame($reply['code'], $updateResponse->replyCode());
        $this->assertSame($reply['message'], $updateResponse->replyMessage());
    }

    public static function validResponseProvider(): array
    {
        return [
            'success with string IDs' => [
                ['ids' => ['123', '124', '125'], 'errors' => []],
                ['code' => 0, 'message' => 'OK'],
                ['123', '124', '125'],
                0,
                false,
            ],
            'success with numeric IDs' => [
                ['ids' => [123, 124, 125], 'errors' => []],
                ['code' => 0, 'message' => 'OK'],
                [123, 124, 125],
                0,
                false,
            ],
            'success with mixed ID types' => [
                ['ids' => ['123', 124, '125'], 'errors' => []],
                ['code' => 0, 'message' => 'OK'],
                ['123', 124, '125'],
                0,
                false,
            ],
            'partial success with errors' => [
                [
                    'ids' => ['123', '124'],
                    'errors' => [
                        ['key' => 'invalid_email', 'errorCode' => 1001, 'errorMsg' => 'Invalid email format'],
                        ['key' => 'missing_field', 'errorCode' => 1002, 'errorMsg' => 'Required field missing'],
                    ],
                ],
                ['code' => 1001, 'message' => 'Partial success'],
                ['123', '124'],
                2,
                true,
            ],
            'failure with empty IDs' => [
                [
                    'ids' => [],
                    'errors' => [
                        ['key' => 'validation_error', 'errorCode' => 2001, 'errorMsg' => 'All contacts failed validation'],
                    ],
                ],
                ['code' => 2001, 'message' => 'All contacts failed validation'],
                [],
                1,
                true,
            ],
        ];
    }

    #[DataProvider('edgeCaseProvider')]
    public function test_from_response_handles_edge_cases(array $responseData): void
    {
        $response = $this->createResponse($responseData, 0, 'OK');
        $updateResponse = UpdateContactsResponse::fromResponse($response);

        $this->assertInstanceOf(UpdateContactsResponse::class, $updateResponse);
        $this->assertFalse($updateResponse->hasErrors());
        $this->assertSame([], $updateResponse->errors);
    }

    public static function edgeCaseProvider(): array
    {
        return [
            'missing errors field' => [['ids' => ['123', '124']]],
            'null errors field' => [['ids' => ['123'], 'errors' => null]],
            'empty errors array' => [['ids' => ['123', '124'], 'errors' => []]],
        ];
    }

    #[DataProvider('invalidResponseProvider')]
    public function test_from_response_throws_exception_for_invalid_data(array $responseData): void
    {
        $response = $this->createResponse($responseData, 0, 'OK');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing "ids" in data response');

        UpdateContactsResponse::fromResponse($response);
    }

    public static function invalidResponseProvider(): array
    {
        return [
            'missing ids field' => [['errors' => []]],
            'null ids field' => [['ids' => null, 'errors' => []]],
        ];
    }

    private function createResponse(array $data, int $replyCode, string $replyMessage): Response
    {
        return new Response(
            reply: new Reply($replyCode, $replyMessage),
            data: $data
        );
    }
}
