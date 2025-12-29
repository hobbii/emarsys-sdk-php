<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\ValueObjects;

use Hobbii\Emarsys\Domain\ValueObjects\Reply;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reply::class)]
final class ReplyTest extends TestCase
{
    #[DataProvider('validReplyDataProvider')]
    public function test_from_response_data_creates_reply_with_valid_data(
        array $data,
        int $expectedCode,
        string $expectedMessage
    ): void {
        $reply = Reply::fromResponseData($data);

        $this->assertInstanceOf(Reply::class, $reply);
        $this->assertSame($expectedCode, $reply->code);
        $this->assertSame($expectedMessage, $reply->message);
    }

    #[DataProvider('invalidReplyDataProvider')]
    public function test_from_response_data_throws_exception_with_invalid_data(
        array $data,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        Reply::fromResponseData($data);
    }

    public function test_constructor_creates_reply_with_given_properties(): void
    {
        $reply = new Reply(code: 0, message: 'OK');

        $this->assertSame(0, $reply->code);
        $this->assertSame('OK', $reply->message);
    }

    /**
     * @return array<string, array{array, int, string}>
     */
    public static function validReplyDataProvider(): array
    {
        return [
            'success response' => [
                ['replyCode' => 0, 'replyText' => 'OK'],
                0,
                'OK',
            ],
            'error response' => [
                ['replyCode' => 1, 'replyText' => 'Invalid request'],
                1,
                'Invalid request',
            ],
            'response with extra fields' => [
                ['replyCode' => 0, 'replyText' => 'OK', 'extraField' => 'ignored'],
                0,
                'OK',
            ],
        ];
    }

    /**
     * @return array<string, array{array, string}>
     */
    public static function invalidReplyDataProvider(): array
    {
        return [
            'missing reply code' => [
                ['replyText' => 'OK'],
                'Invalid response structure: missing replyCode',
            ],
            'reply code is null' => [
                ['replyCode' => null, 'replyText' => 'OK'],
                'Invalid response structure: missing replyCode',
            ],
            'reply code is string' => [
                ['replyCode' => '0', 'replyText' => 'OK'],
                'Invalid response structure: missing replyCode',
            ],
            'reply code is float' => [
                ['replyCode' => 0.5, 'replyText' => 'OK'],
                'Invalid response structure: missing replyCode',
            ],
            'empty array' => [
                [],
                'Invalid response structure: missing replyCode',
            ],
            'missing reply text' => [
                ['replyCode' => 0],
                'Invalid response structure: missing replyText',
            ],
            'empty reply text' => [
                ['replyCode' => 2, 'replyText' => ''],
                'Invalid response structure: missing replyText',
            ],
        ];
    }
}
