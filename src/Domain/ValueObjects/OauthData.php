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
        $this->expiresAt = time() + $expiresIn - 60; // Refresh 1 minute early
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
}
