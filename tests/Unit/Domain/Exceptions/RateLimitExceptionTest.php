<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\Exceptions;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Hobbii\Emarsys\Domain\Exceptions\RateLimitException;
use PHPUnit\Framework\TestCase;

class RateLimitExceptionTest extends TestCase
{
    public function test_from_psr_response_with_all_headers(): void
    {
        $resetTimestamp = time() + 120;

        $response = new GuzzleResponse(429, [
            'Retry-After' => '120',
            'X-RateLimit-Reset' => (string) $resetTimestamp,
            'X-RateLimit-Limit' => '1000',
            'X-RateLimit-Remaining' => '0',
        ]);

        $exception = RateLimitException::fromPsrResponse($response);

        $this->assertInstanceOf(RateLimitException::class, $exception);
        $this->assertSame('Rate limit exceeded. Please retry after 120 seconds', $exception->getMessage());
        $this->assertSame(120, $exception->retryAfterSeconds);
        $this->assertSame($resetTimestamp, $exception->resetTimestamp);
        $this->assertSame(0, $exception->limitRemaining);
        $this->assertSame(1000, $exception->limitTotal);
    }

    public function test_from_psr_response_with_retry_after_integer(): void
    {
        $response = new GuzzleResponse(429, [
            'Retry-After' => '300',
        ]);

        $exception = RateLimitException::fromPsrResponse($response);

        $this->assertSame(300, $exception->retryAfterSeconds);
        $this->assertNull($exception->resetTimestamp);
        $this->assertNull($exception->limitRemaining);
        $this->assertNull($exception->limitTotal);
    }

    public function test_from_psr_response_with_retry_after_http_date(): void
    {
        $futureTime = time() + 600; // 10 minutes from now
        $httpDate = gmdate('D, d M Y H:i:s T', $futureTime);

        $response = new GuzzleResponse(429, [
            'Retry-After' => $httpDate,
        ]);

        $exception = RateLimitException::fromPsrResponse($response);

        // Allow 5 second margin for test execution time
        $this->assertGreaterThanOrEqual(595, $exception->retryAfterSeconds);
        $this->assertLessThanOrEqual(605, $exception->retryAfterSeconds);
    }

    public function test_from_psr_response_with_x_rate_limit_reset(): void
    {
        $resetTimestamp = time() + 180; // 3 minutes from now

        $response = new GuzzleResponse(429, [
            'X-RateLimit-Reset' => (string) $resetTimestamp,
            'X-RateLimit-Limit' => '500',
        ]);

        $exception = RateLimitException::fromPsrResponse($response);

        // Verify retry-after is calculated from reset timestamp
        $this->assertGreaterThanOrEqual(175, $exception->retryAfterSeconds);
        $this->assertLessThanOrEqual(185, $exception->retryAfterSeconds);
        $this->assertSame($resetTimestamp, $exception->resetTimestamp);
        $this->assertSame(500, $exception->limitTotal);
    }

    public function test_from_psr_response_with_no_headers_uses_defaults(): void
    {
        $response = new GuzzleResponse(429);

        $exception = RateLimitException::fromPsrResponse($response);

        // Default retry-after is 60 seconds
        $this->assertSame(60, $exception->retryAfterSeconds);
        $this->assertNull($exception->resetTimestamp);
        $this->assertNull($exception->limitRemaining);
        $this->assertNull($exception->limitTotal);
    }

    public function test_from_psr_response_with_previous_exception(): void
    {
        $response = new GuzzleResponse(429, ['Retry-After' => '60']);
        $previousException = new \Exception('Previous error');

        $exception = RateLimitException::fromPsrResponse($response, $previousException);

        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function test_from_psr_response_prioritizes_retry_after_over_reset(): void
    {
        $resetTimestamp = time() + 300; // 5 minutes

        $response = new GuzzleResponse(429, [
            'Retry-After' => '120', // 2 minutes
            'X-RateLimit-Reset' => (string) $resetTimestamp,
        ]);

        $exception = RateLimitException::fromPsrResponse($response);

        // Retry-After header should take precedence
        $this->assertSame(120, $exception->retryAfterSeconds);
        $this->assertSame($resetTimestamp, $exception->resetTimestamp);
    }

    public function test_from_psr_response_with_only_rate_limit_headers(): void
    {
        $response = new GuzzleResponse(429, [
            'X-RateLimit-Limit' => '1000',
            'X-RateLimit-Remaining' => '5',
        ]);

        $exception = RateLimitException::fromPsrResponse($response);

        // Should use default retry-after
        $this->assertSame(60, $exception->retryAfterSeconds);
        $this->assertSame(5, $exception->limitRemaining);
        $this->assertSame(1000, $exception->limitTotal);
    }
}
