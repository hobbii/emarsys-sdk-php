<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Exceptions;

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
}
