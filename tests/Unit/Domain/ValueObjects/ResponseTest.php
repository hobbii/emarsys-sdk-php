<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\ValueObjects;

use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\ValueObjects\Reply;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

#[CoversClass(Response::class)]
final class ResponseTest extends TestCase
{
    #[DataProvider('dataAccessorProvider')]
    public function test_data_accessor_methods(
        int|string|array|null $data,
        string $method,
        ?string $expectedExceptionMessage = null
    ): void {
        $response = new Response(
            reply: new Reply(0, 'OK'),
            data: $data
        );

        if ($expectedExceptionMessage) {
            $this->expectException(ApiException::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $result = $response->$method();

        if (! $expectedExceptionMessage) {
            $this->assertSame($data, $result);
        }
    }

    #[DataProvider('dataGetProvider')]
    public function test_data_get_method(
        int|string|array|null $data,
        string $key,
        mixed $default,
        mixed $expected,
        ?string $expectedExceptionMessage = null
    ): void {
        $response = new Response(
            reply: new Reply(0, 'OK'),
            data: $data
        );

        if ($expectedExceptionMessage) {
            $this->expectException(ApiException::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $result = $response->data($key, $default);

        if (! $expectedExceptionMessage) {
            $this->assertSame($expected, $result);
        }
    }

    #[DataProvider('fromPsrResponseProvider')]
    public function test_from_psr_response_handles_various_scenarios(
        string $responseBody,
        int $expectedReplyCode,
        string $expectedReplyMessage,
        int|string|array|null $expectedData,
        ?string $expectedExceptionMessage = null
    ): void {
        $psrResponse = $this->createPsrResponseMock($responseBody);

        if ($expectedExceptionMessage) {
            $this->expectException(ApiException::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $response = Response::fromPsrResponse($psrResponse);

        if (! $expectedExceptionMessage) {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertSame($expectedReplyCode, $response->reply->code);
            $this->assertSame($expectedReplyMessage, $response->reply->message);
            $this->assertSame($expectedData, $response->data);
        }
    }

    public function test_constructor_creates_response_with_given_properties(): void
    {
        $reply = new Reply(0, 'OK');
        $data = ['key' => 'value'];

        $response = new Response($reply, $data);

        $this->assertSame($reply, $response->reply);
        $this->assertSame($data, $response->data);
    }

    /**
     * @return array<string, array{int|string|array|null, string, ?string}>
     */
    public static function dataAccessorProvider(): array
    {
        return [
            'dataAsInt with valid int' => [
                42,
                'dataAsInt',
                null,
            ],
            'dataAsInt with string throws exception' => [
                'not an int',
                'dataAsInt',
                'Response data is not an integer',
            ],
            'dataAsInt with array throws exception' => [
                ['key' => 'value'],
                'dataAsInt',
                'Response data is not an integer',
            ],
            'dataAsInt with null throws exception' => [
                null,
                'dataAsInt',
                'Response data is not an integer',
            ],
            'dataAsString with valid string' => [
                'test string',
                'dataAsString',
                null,
            ],
            'dataAsString with int throws exception' => [
                123,
                'dataAsString',
                'Response data is not a string',
            ],
            'dataAsString with array throws exception' => [
                ['key' => 'value'],
                'dataAsString',
                'Response data is not a string',
            ],
            'dataAsString with null throws exception' => [
                null,
                'dataAsString',
                'Response data is not a string',
            ],
            'dataAsArray with valid array' => [
                ['key' => 'value', 'number' => 123],
                'dataAsArray',
                null,
            ],
            'dataAsArray with empty array' => [
                [],
                'dataAsArray',
                null,
            ],
            'dataAsArray with string throws exception' => [
                'not an array',
                'dataAsArray',
                'Response data is not an array',
            ],
            'dataAsArray with int throws exception' => [
                42,
                'dataAsArray',
                'Response data is not an array',
            ],
            'dataAsArray with null throws exception' => [
                null,
                'dataAsArray',
                'Response data is not an array',
            ],
        ];
    }

    /**
     * @return array<string, array{int|string|array|null, string, mixed, mixed, ?string}>
     */
    public static function dataGetProvider(): array
    {
        return [
            'existing key returns value' => [
                ['name' => 'John', 'age' => 30],
                'name',
                null,
                'John',
                null,
            ],
            'missing key returns default' => [
                ['name' => 'John'],
                'age',
                25,
                25,
                null,
            ],
            'missing key with null default' => [
                ['name' => 'John'],
                'age',
                null,
                null,
                null,
            ],
            'empty array returns default' => [
                [],
                'missing',
                'default',
                'default',
                null,
            ],
            'non-array data throws exception' => [
                'not an array',
                'key',
                null,
                null,
                'Response data is not an array',
            ],
            'null data throws exception' => [
                null,
                'key',
                null,
                null,
                'Response data is not an array',
            ],
        ];
    }

    /**
     * @return array<string, array{string, int, string, int|string|array|null, ?string}>
     */
    public static function fromPsrResponseProvider(): array
    {
        return [
            'valid response with data' => [
                '{"replyCode": 0, "replyText": "OK", "data": {"id": 123, "name": "Test"}}',
                0,
                'OK',
                ['id' => 123, 'name' => 'Test'],
                null,
            ],
            'valid response without data' => [
                '{"replyCode": 0, "replyText": "OK"}',
                0,
                'OK',
                null,
                null,
            ],
            'error response' => [
                '{"replyCode": 1, "replyText": "Invalid request", "data": null}',
                1,
                'Invalid request',
                null,
                null,
            ],
            'response with null data' => [
                '{"replyCode": 0, "replyText": "OK", "data": null}',
                0,
                'OK',
                null,
                null,
            ],
            'response with string data' => [
                '{"replyCode": 0, "replyText": "OK", "data": "string value"}',
                0,
                'OK',
                'string value',
                null,
            ],
            'response with int data' => [
                '{"replyCode": 0, "replyText": "OK", "data": 42}',
                0,
                'OK',
                42,
                null,
            ],
            'invalid json throws exception' => [
                '{"invalid": json}',
                0,
                'OK',
                null,
                'Invalid JSON response',
            ],
            'non-array json throws exception' => [
                '"just a string"',
                0,
                'OK',
                null,
                'Invalid JSON response',
            ],
            'empty response throws exception' => [
                '',
                0,
                'OK',
                null,
                'Invalid JSON response',
            ],
        ];
    }

    private function createPsrResponseMock(string $body): ResponseInterface
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        return $response;
    }
}
