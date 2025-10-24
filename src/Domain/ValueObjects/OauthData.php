<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ValueObjects;

use InvalidArgumentException;

readonly class OauthData
{
    private int $expiresAt;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresIn = 3600, // Default to 1 hour
        public ?string $scope = null,
    ) {
        $this->validateAccessToken();
        $this->expiresAt = $this->calculateExpiresAt($expiresIn);
    }

    private function validateAccessToken(): void
    {
        if (empty($this->accessToken)) {
            throw new InvalidArgumentException('Access token cannot be empty');
        }
    }

    public function isExpired(): bool
    {
        return time() >= $this->expiresAt;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $arr): self
    {
        if (! isset($arr['access_token'], $arr['token_type'], $arr['expires_in'])) {
            throw new InvalidArgumentException('Missing required fields: access_token, token_type, expires_in');
        }

        return new self(
            accessToken: $arr['access_token'],
            tokenType: $arr['token_type'],
            expiresIn: (int) $arr['expires_in'],
            scope: $arr['scope'] ?? null,
        );
    }

    /**
     * Calculate the expiration timestamp with appropriate safety buffer.
     *
     * IMPORTANT: This method assumes that expiresIn represents the remaining lifetime
     * from the current moment (time()). This is typically correct when processing
     * an OAuth response immediately, but may be inaccurate if there are significant
     * delays between receiving the OAuth response and constructing this object.
     *
     * For production use, minimize the time between OAuth response and object construction.
     * If you need to account for known delays, consider subtracting them from expiresIn
     * before passing to the constructor.
     *
     * @param  int  $expiresIn  Token lifetime in seconds from now
     * @return int Unix timestamp when token should be considered expired
     */
    private function calculateExpiresAt(int $expiresIn): int
    {
        // Use a progressive safety buffer that preserves usable lifetime while preventing race conditions
        if ($expiresIn >= 300) {
            // Long-lived tokens (>=5 min): 60s buffer
            $safetyBuffer = 60;
        } elseif ($expiresIn > 60) {
            // Medium tokens (1-5 min): 20% buffer (max 60s)
            $safetyBuffer = min(60, (int) ($expiresIn * 0.2));
        } elseif ($expiresIn > 30) {
            // Short tokens (30s-1min): 10% buffer (max 6s)
            $safetyBuffer = min(6, (int) ($expiresIn * 0.1));
        } elseif ($expiresIn > 10) {
            // Short tokens (10-30s): 10% buffer (minimum 1s, maximum 3s)
            $safetyBuffer = max(1, min(3, (int) ($expiresIn * 0.1)));
        } else {
            // Very short tokens (≤10s): apply minimal buffer to preserve usability
            // For tokens ≤ 2s: no buffer (accept race condition risk for functionality)
            // For tokens 3-10s: 1s buffer (minimal safety vs network latency)
            $safetyBuffer = $expiresIn <= 2 ? 0 : 1;
        }

        return time() + $expiresIn - $safetyBuffer;
    }
}
