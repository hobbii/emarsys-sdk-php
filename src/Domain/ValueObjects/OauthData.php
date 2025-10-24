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
        $this->setupExpiredAt();
    }

    private function validateAccessToken(): void
    {
        if (empty($this->accessToken)) {
            throw new InvalidArgumentException('Access token cannot be empty');
        }
    }

    private function setupExpiredAt(): void
    {
        // Use a safety buffer that doesn't exceed the token lifetime
        // For tokens > 120s: 60s buffer, for shorter tokens: 50% of lifetime (minimum 5s)
        $safetyBuffer = $this->expiresIn > 120 ? 60 : max(5, (int) ($this->expiresIn * 0.5));
        $this->expiresAt = time() + $this->expiresIn - $safetyBuffer;
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
}
