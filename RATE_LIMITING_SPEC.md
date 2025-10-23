# Rate Limiting - Full Implementation Specification

## Overview

This document outlines the specification for implementing automatic rate limit handling with exponential backoff in the Emarsys SDK. This is intended as a future enhancement beyond the minimal implementation in v1.0.0-RC1.

## Current Implementation (v1.0.0-RC1)

The minimal implementation provides:

- ✅ `RateLimitException` thrown on 429 responses
- ✅ Extraction of `Retry-After` header (both integer seconds and HTTP date format)
- ✅ Extraction of `X-RateLimit-Reset` header
- ✅ Extraction of `X-RateLimit-Limit` and `X-RateLimit-Remaining` headers
- ✅ Default 60-second retry fallback
- ✅ Manual retry logic (users implement their own retry handling)

## Proposed Full Implementation

### 1. Automatic Retry with Exponential Backoff

**Goal:** Automatically retry rate-limited requests with intelligent backoff strategy.

#### Configuration Options

Add new constructor parameters to `Client`:

```php
public function __construct(
    private readonly string $clientId,
    private readonly string $clientSecret,
    ?string $baseUrl = null,
    ?GuzzleClient $client = null,
    private readonly bool $autoRetryOnRateLimit = true,    // NEW
    private readonly int $maxRetries = 3,                   // NEW
    private readonly float $backoffMultiplier = 2.0,        // NEW
) {
    // ...
}
```

**Parameters:**
- `autoRetryOnRateLimit`: Enable automatic retry on rate limit (default: `true`)
- `maxRetries`: Maximum number of retry attempts (default: `3`)
- `backoffMultiplier`: Exponential backoff multiplier (default: `2.0`)

#### Retry Logic

```php
private function makeRequest(
    string $method, 
    string $endpoint, 
    array $options = [], 
    bool $isRetry = false,
    int $rateLimitRetryCount = 0
): Response {
    // ... existing OAuth and request code ...
    
    try {
        $response = $this->client->request($method, $endpoint, $options);
        return Response::fromPsrResponse($response);
    } catch (ClientException $e) {
        $statusCode = $e->getResponse()->getStatusCode();
        
        // ... existing 401, 403 handling ...
        
        if ($statusCode === 429 && $this->autoRetryOnRateLimit) {
            if ($rateLimitRetryCount < $this->maxRetries) {
                $retryAfter = $this->extractRetryAfter($e->getResponse());
                
                // Apply exponential backoff
                $backoffSeconds = $retryAfter * pow($this->backoffMultiplier, $rateLimitRetryCount);
                
                // Optional: Add jitter to prevent thundering herd
                $jitter = random_int(0, (int)($backoffSeconds * 0.1)); // 10% jitter
                $finalWaitTime = (int)($backoffSeconds + $jitter);
                
                // Log the retry (if logger is available)
                $this->logRateLimitRetry($rateLimitRetryCount + 1, $finalWaitTime);
                
                sleep($finalWaitTime);
                
                return $this->makeRequest(
                    $method, 
                    $endpoint, 
                    $options, 
                    $isRetry,
                    $rateLimitRetryCount + 1
                );
            }
            
            // Max retries exceeded
            throw new RateLimitException(
                message: "Rate limit exceeded after {$this->maxRetries} retries",
                retryAfterSeconds: $this->extractRetryAfter($e->getResponse()),
                limitRemaining: $this->extractRateLimitHeader($e->getResponse(), 'X-RateLimit-Remaining'),
                limitTotal: $this->extractRateLimitHeader($e->getResponse(), 'X-RateLimit-Limit'),
                previous: $e
            );
        }
        
        if ($statusCode === 429) {
            // Auto-retry disabled, throw immediately
            throw new RateLimitException(/* ... */);
        }
        
        // ... rest of exception handling
    }
}
```

### 2. Proactive Rate Limit Tracking

**Goal:** Track rate limits proactively to avoid hitting limits.

#### Add Rate Limit Metadata to Response

Enhance `Response` value object:

```php
readonly class Response
{
    public function __construct(
        public int $replyCode,
        public string $replyText,
        public int|string|array|null $data,
        public array $errors,
        public ?RateLimitInfo $rateLimitInfo = null,  // NEW
    ) {}
}
```

Create new value object:

```php
readonly class RateLimitInfo
{
    public function __construct(
        public ?int $limit = null,
        public ?int $remaining = null,
        public ?int $resetTimestamp = null,
    ) {}
    
    public function isNearLimit(float $threshold = 0.1): bool
    {
        if ($this->limit === null || $this->remaining === null) {
            return false;
        }
        
        return ($this->remaining / $this->limit) <= $threshold;
    }
    
    public function secondsUntilReset(): ?int
    {
        if ($this->resetTimestamp === null) {
            return null;
        }
        
        return max(0, $this->resetTimestamp - time());
    }
}
```

Update `Response::fromPsrResponse()`:

```php
public static function fromPsrResponse(ResponseInterface $response): self
{
    // ... existing JSON parsing ...
    
    $rateLimitInfo = new RateLimitInfo(
        limit: $response->hasHeader('X-RateLimit-Limit')
            ? (int) $response->getHeader('X-RateLimit-Limit')[0]
            : null,
        remaining: $response->hasHeader('X-RateLimit-Remaining')
            ? (int) $response->getHeader('X-RateLimit-Remaining')[0]
            : null,
        resetTimestamp: $response->hasHeader('X-RateLimit-Reset')
            ? (int) $response->getHeader('X-RateLimit-Reset')[0]
            : null,
    );
    
    return new self(
        replyCode: $data['replyCode'] ?? 0,
        replyText: $data['replyText'] ?? '',
        data: $data['data'] ?? null,
        errors: $errors,
        rateLimitInfo: $rateLimitInfo,
    );
}
```

#### Usage Example

```php
$response = $client->contactLists()->list();

if ($response->rateLimitInfo?->isNearLimit(0.2)) {
    // Only 20% of rate limit remaining
    echo "Warning: Approaching rate limit\n";
    echo "Remaining: {$response->rateLimitInfo->remaining}/{$response->rateLimitInfo->limit}\n";
    echo "Resets in: {$response->rateLimitInfo->secondsUntilReset()} seconds\n";
    
    // Optionally slow down or pause
    sleep(5);
}
```

### 3. PSR-3 Logger Integration

**Goal:** Provide visibility into rate limit events.

#### Add Logger Support

```php
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

public function __construct(
    private readonly string $clientId,
    private readonly string $clientSecret,
    ?string $baseUrl = null,
    ?GuzzleClient $client = null,
    private readonly bool $autoRetryOnRateLimit = true,
    private readonly int $maxRetries = 3,
    private readonly float $backoffMultiplier = 2.0,
    private readonly LoggerInterface $logger = new NullLogger(),  // NEW
) {
    // ...
}
```

#### Log Rate Limit Events

```php
private function logRateLimitRetry(int $attemptNumber, int $waitSeconds): void
{
    $this->logger->warning('Rate limit exceeded, retrying', [
        'attempt' => $attemptNumber,
        'max_attempts' => $this->maxRetries,
        'wait_seconds' => $waitSeconds,
    ]);
}

// In makeRequest() after successful retry:
$this->logger->info('Rate limit retry succeeded', [
    'attempt' => $rateLimitRetryCount + 1,
]);

// When max retries exceeded:
$this->logger->error('Rate limit max retries exceeded', [
    'max_attempts' => $this->maxRetries,
]);
```

### 4. Rate Limit Events/Callbacks

**Goal:** Allow custom handling of rate limit events.

#### Add Event Callbacks

```php
public function __construct(
    // ... existing parameters ...
    private readonly ?callable $onRateLimitRetry = null,     // NEW
    private readonly ?callable $onRateLimitExceeded = null,  // NEW
) {
    // ...
}
```

#### Trigger Callbacks

```php
// Before retrying:
if ($this->onRateLimitRetry !== null) {
    ($this->onRateLimitRetry)($rateLimitRetryCount + 1, $finalWaitTime, $e->getResponse());
}

// When max retries exceeded:
if ($this->onRateLimitExceeded !== null) {
    ($this->onRateLimitExceeded)($this->maxRetries, $e->getResponse());
}
```

#### Usage Example

```php
$client = new Client(
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret',
    onRateLimitRetry: function(int $attempt, int $waitSeconds, ResponseInterface $response) {
        echo "Rate limit hit! Retrying attempt {$attempt} after {$waitSeconds}s\n";
        
        // Send to monitoring system
        Monitoring::increment('emarsys.rate_limit.retry');
    },
    onRateLimitExceeded: function(int $maxAttempts, ResponseInterface $response) {
        echo "Rate limit exceeded after {$maxAttempts} attempts\n";
        
        // Alert operations team
        Alert::send('Emarsys rate limit exceeded');
    }
);
```

## Implementation Phases

### Phase 1: Basic Auto-Retry (v1.1.0)
- Automatic retry with exponential backoff
- Configurable `autoRetryOnRateLimit`, `maxRetries`, `backoffMultiplier`
- Update README with auto-retry examples
- Comprehensive unit tests

**Estimated Effort:** 4-6 hours

### Phase 2: Proactive Rate Limit Tracking (v1.2.0)
- `RateLimitInfo` value object
- Include rate limit metadata in all responses
- Helper methods (`isNearLimit()`, `secondsUntilReset()`)
- Update README with proactive handling examples

**Estimated Effort:** 3-4 hours

### Phase 3: Observability (v1.3.0)
- PSR-3 logger integration
- Rate limit event callbacks
- Update README with logging/monitoring examples
- Add `symfony/psr-http-message-bridge` as optional dependency

**Estimated Effort:** 2-3 hours

## Testing Requirements

### Unit Tests
- Automatic retry with various backoff scenarios
- Max retries exceeded behavior
- Jitter calculation (if implemented)
- Logger calls verification
- Callback invocations
- `RateLimitInfo` helper methods

### Integration Tests
- Real API rate limit scenarios (if possible in test environment)
- Verify actual sleep/wait behavior
- Confirm retry headers are correctly extracted

## Documentation Updates

### README.md

Add sections:
- **Automatic Rate Limit Retry** (configuration, examples)
- **Rate Limit Monitoring** (proactive tracking, callbacks)
- **Best Practices** (handling high-volume scenarios)

### Migration Guide

Document changes for users upgrading from v1.0.x:
- Auto-retry is enabled by default (breaking change consideration)
- How to disable auto-retry if desired
- New configuration options

## Breaking Changes Considerations

### Default Behavior
**Decision Required:** Should auto-retry be **enabled** or **disabled** by default?

**Option A: Enabled by Default (Recommended)**
- **Pros:** Better UX, handles rate limits transparently
- **Cons:** May mask rate limit issues, unexpected sleeps
- **Migration:** Most users benefit, explicit opt-out available

**Option B: Disabled by Default**
- **Pros:** Explicit opt-in, no surprises
- **Cons:** Less convenient, requires user action
- **Migration:** No breaking changes from v1.0.x

**Recommendation:** Enable by default (`autoRetryOnRateLimit = true`) with clear documentation.

### Semantic Versioning
- **v1.1.0:** Add auto-retry (minor version bump)
- **v1.2.0:** Add proactive tracking (minor version bump)
- **v1.3.0:** Add observability (minor version bump)
- **v2.0.0:** Only if breaking changes required (e.g., changing default behavior)

## Performance Considerations

1. **Sleep vs. Async:** Current implementation uses `sleep()`, blocking the request. Consider async alternatives for future versions.

2. **Memory:** Rate limit retry count adds minimal memory overhead (single integer per request).

3. **Timeouts:** Exponential backoff can cause long waits. Consider:
   - Maximum total wait time (e.g., 5 minutes)
   - Respect PHP's `max_execution_time`

4. **Jitter:** Adding randomness prevents thundering herd problem when multiple clients hit rate limit simultaneously.

## Security Considerations

1. **No Credential Logging:** Ensure logger never logs credentials
2. **Rate Limit Headers:** Some APIs consider rate limit headers sensitive
3. **Denial of Service:** Max retries prevents infinite loops

## Open Questions

1. Should we implement request queuing for high-volume scenarios?
2. Should we provide a `RateLimiter` interface for custom implementations?
3. Should we track rate limits across multiple client instances (requires external storage)?
4. Should we add metrics/telemetry (Prometheus, StatsD)?

## References

- [RFC 6585 - Additional HTTP Status Codes](https://tools.ietf.org/html/rfc6585#section-4)
- [Emarsys API Documentation](https://dev.emarsys.com/docs/emarsys-core-api-guides/cd1025a4f6b0d-miscellaneous)
- [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/)
- [Exponential Backoff and Jitter](https://aws.amazon.com/blogs/architecture/exponential-backoff-and-jitter/)

