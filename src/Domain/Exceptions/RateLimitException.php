<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Exceptions;

use Psr\Http\Message\ResponseInterface;

/**
 * Exception thrown when API rate limit is exceeded.
 *
 * The Emarsys API enforces rate limits to ensure fair usage and system stability.
 * When the rate limit is exceeded, the API returns a 429 (Too Many Requests) response.
 *
 * Emarsys Rate Limit Headers:
 * - X-RateLimit-Limit: Request limit per minute
 * - X-Ratelimit-Remaining: The number of requests left for the time window
 * - X-RateLimit-Reset: The time when the rate limit window resets (Unix timestamp)
 *
 * This exception includes information about when the request can be retried:
 * - retryAfterSeconds: Number of seconds to wait before retrying
 * - resetTimestamp: Unix timestamp when rate limit resets (from X-RateLimit-Reset)
 * - limitRemaining: Number of requests remaining in current time window
 * - limitTotal: Total number of requests allowed in time window (per minute)
 */
class RateLimitException extends ApiException
{
    public function __construct(
        string $message = 'Rate limit exceeded',
        public readonly ?int $retryAfterSeconds = null,
        public readonly ?int $resetTimestamp = null,
        public readonly ?int $limitRemaining = null,
        public readonly ?int $limitTotal = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, previous: $previous);
    }

    /**
     * Create a RateLimitException from a PSR-7 response.
     *
     * Extracts rate limit information from response headers and creates
     * an exception with all relevant metadata.
     */
    public static function fromPsrResponse(ResponseInterface $response, ?\Throwable $previous = null): self
    {
        $retryAfter = self::extractRetryAfter($response);
        $resetTimestamp = self::extractRateLimitHeader($response, 'X-RateLimit-Reset');
        $limitRemaining = self::extractRateLimitHeader($response, 'X-RateLimit-Remaining');
        $limitTotal = self::extractRateLimitHeader($response, 'X-RateLimit-Limit');

        return new self(
            message: 'Rate limit exceeded. Please retry after '.$retryAfter.' seconds',
            retryAfterSeconds: $retryAfter,
            resetTimestamp: $resetTimestamp,
            limitRemaining: $limitRemaining,
            limitTotal: $limitTotal,
            previous: $previous
        );
    }

    /**
     * Extract retry-after value from rate limit response.
     *
     * Checks both Retry-After header (standard) and X-RateLimit-Reset header (common in APIs).
     * The Retry-After header can contain either:
     * - An integer representing seconds to wait
     * - An HTTP date string representing when to retry
     *
     * @return int Number of seconds to wait before retrying (defaults to 60 if not found)
     */
    private static function extractRetryAfter(ResponseInterface $response): int
    {
        // Check standard Retry-After header
        if ($response->hasHeader('Retry-After')) {
            $retryAfter = $response->getHeader('Retry-After')[0];

            // Can be seconds (integer) or HTTP date
            if (is_numeric($retryAfter)) {
                return (int) $retryAfter;
            }

            // Parse HTTP date and calculate seconds
            $timestamp = strtotime($retryAfter);
            if ($timestamp !== false) {
                return max(0, $timestamp - time());
            }
        }

        // Check X-RateLimit-Reset header (common in APIs)
        if ($response->hasHeader('X-RateLimit-Reset')) {
            $resetTimestamp = (int) $response->getHeader('X-RateLimit-Reset')[0];

            return max(0, $resetTimestamp - time());
        }

        // Default fallback: wait 60 seconds
        return 60;
    }

    /**
     * Extract a specific rate limit header value from the response.
     *
     * @return int|null The header value as an integer, or null if not present
     */
    private static function extractRateLimitHeader(ResponseInterface $response, string $headerName): ?int
    {
        if ($response->hasHeader($headerName)) {
            return (int) $response->getHeader($headerName)[0];
        }

        return null;
    }
}
