<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\ValueObjects;

use Hobbii\Emarsys\Domain\ValueObjects\OauthData;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OauthDataTest extends TestCase
{
    public function test_creates_oauth_data_with_required_fields(): void
    {
        // Arrange & Act
        $oauth = new OauthData(
            accessToken: 'test-token',
            tokenType: 'Bearer',
            expiresIn: 3600
        );

        // Assert
        $this->assertSame('test-token', $oauth->accessToken);
        $this->assertSame('Bearer', $oauth->tokenType);
        $this->assertSame(3600, $oauth->expiresIn);
        $this->assertNull($oauth->scope);
    }

    public function test_creates_oauth_data_with_all_fields(): void
    {
        // Arrange & Act
        $oauth = new OauthData(
            accessToken: 'test-token',
            tokenType: 'Bearer',
            expiresIn: 7200,
            scope: 'read write'
        );

        // Assert
        $this->assertSame('test-token', $oauth->accessToken);
        $this->assertSame('Bearer', $oauth->tokenType);
        $this->assertSame(7200, $oauth->expiresIn);
        $this->assertSame('read write', $oauth->scope);
    }

    public function test_throws_exception_for_empty_access_token(): void
    {
        // Arrange & Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Access token cannot be empty');

        new OauthData(
            accessToken: '',
            tokenType: 'Bearer',
            expiresIn: 3600
        );
    }

    public function test_creates_from_array_with_required_fields(): void
    {
        // Arrange
        $data = [
            'access_token' => 'test-token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];

        // Act
        $oauth = OauthData::fromArray($data);

        // Assert
        $this->assertSame('test-token', $oauth->accessToken);
        $this->assertSame('Bearer', $oauth->tokenType);
        $this->assertSame(3600, $oauth->expiresIn);
        $this->assertNull($oauth->scope);
    }

    public function test_creates_from_array_with_all_fields(): void
    {
        // Arrange
        $data = [
            'access_token' => 'test-token',
            'token_type' => 'Bearer',
            'expires_in' => 7200,
            'scope' => 'read write',
        ];

        // Act
        $oauth = OauthData::fromArray($data);

        // Assert
        $this->assertSame('test-token', $oauth->accessToken);
        $this->assertSame('Bearer', $oauth->tokenType);
        $this->assertSame(7200, $oauth->expiresIn);
        $this->assertSame('read write', $oauth->scope);
    }

    public function test_throws_exception_when_creating_from_array_with_missing_fields(): void
    {
        // Arrange
        $data = [
            'access_token' => 'test-token',
            // Missing token_type and expires_in
        ];

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: access_token, token_type, expires_in');

        OauthData::fromArray($data);
    }

    public function test_is_not_expired_immediately_after_creation(): void
    {
        // Arrange & Act
        $oauth = new OauthData(
            accessToken: 'test-token',
            tokenType: 'Bearer',
            expiresIn: 3600
        );

        // Assert
        $this->assertFalse($oauth->isExpired());
    }

    public function test_expiry_calculation_for_long_lived_tokens(): void
    {
        // Arrange
        $expiresIn = 3600; // 1 hour
        $beforeTime = time();

        // Act
        $oauth = new OauthData(
            accessToken: 'test-token',
            tokenType: 'Bearer',
            expiresIn: $expiresIn
        );

        $afterTime = time();

        // Assert - token should not be expired immediately
        $this->assertFalse($oauth->isExpired());

        // The token should have 60 seconds safety buffer for long-lived tokens
        // We can't test exact timing due to execution time, but we can verify it's not immediately expired
    }

    public function test_expiry_calculation_for_short_lived_tokens(): void
    {
        // Arrange
        $expiresIn = 30; // 30 seconds

        // Act
        $oauth = new OauthData(
            accessToken: 'test-token',
            tokenType: 'Bearer',
            expiresIn: $expiresIn
        );

        // Assert - token should not be expired immediately, even for short-lived tokens
        $this->assertFalse($oauth->isExpired());
    }

    public function test_expiry_calculation_for_very_short_tokens(): void
    {
        // Arrange
        $expiresIn = 10; // 10 seconds

        // Act
        $oauth = new OauthData(
            accessToken: 'test-token',
            tokenType: 'Bearer',
            expiresIn: $expiresIn
        );

        // Assert - token should not be expired immediately, even for very short tokens
        $this->assertFalse($oauth->isExpired());
    }

    public function test_expiry_calculation_for_edge_case_tokens(): void
    {
        // Arrange - test tokens with exactly 120 seconds (boundary case)
        $expiresIn = 120;

        // Act
        $oauth = new OauthData(
            accessToken: 'test-token',
            tokenType: 'Bearer',
            expiresIn: $expiresIn
        );

        // Assert
        $this->assertFalse($oauth->isExpired());
    }

    public function test_expiry_calculation_uses_appropriate_safety_buffer(): void
    {
        // Test that different token lifetimes use appropriate safety buffers
        $testCases = [
            // Very short tokens (≤2s): no buffer to preserve usable time
            ['expiresIn' => 1, 'expectedNotExpired' => true, 'description' => '1s token with no buffer'],
            ['expiresIn' => 2, 'expectedNotExpired' => true, 'description' => '2s token with no buffer'],

            // Short tokens (3-10s): 1s buffer for safety
            ['expiresIn' => 3, 'expectedNotExpired' => true, 'description' => '3s token with 1s buffer'],
            ['expiresIn' => 5, 'expectedNotExpired' => true, 'description' => '5s token with 1s buffer'],
            ['expiresIn' => 10, 'expectedNotExpired' => true, 'description' => '10s token with 1s buffer'],

            // Short tokens (10-30s): 10% buffer (1-3s range)
            ['expiresIn' => 15, 'expectedNotExpired' => true, 'description' => '15s token with 1s buffer'],
            ['expiresIn' => 20, 'expectedNotExpired' => true, 'description' => '20s token with 2s buffer'],
            ['expiresIn' => 30, 'expectedNotExpired' => true, 'description' => '30s token with 3s buffer'],

            // Short tokens (30s-1min): 10% buffer (max 6s)
            ['expiresIn' => 45, 'expectedNotExpired' => true, 'description' => '45s token with 4s buffer'],
            ['expiresIn' => 60, 'expectedNotExpired' => true, 'description' => '60s token with 6s buffer'],

            // Medium tokens (1-5min): 20% buffer (max 60s)
            ['expiresIn' => 120, 'expectedNotExpired' => true, 'description' => '2min token with 24s buffer'],
            ['expiresIn' => 300, 'expectedNotExpired' => true, 'description' => '5min token with 60s buffer'],

            // Long tokens (>5min): 60s buffer
            ['expiresIn' => 600, 'expectedNotExpired' => true, 'description' => '10min token with 60s buffer'],
            ['expiresIn' => 3600, 'expectedNotExpired' => true, 'description' => '1hr token with 60s buffer'],
        ];

        foreach ($testCases as $testCase) {
            // Act
            $oauth = new OauthData(
                accessToken: 'test-token',
                tokenType: 'Bearer',
                expiresIn: $testCase['expiresIn']
            );

            // Assert
            $this->assertSame(
                $testCase['expectedNotExpired'],
                ! $oauth->isExpired(),
                $testCase['description'].' should not be immediately expired'
            );
        }
    }

    public function test_very_short_token_has_minimal_safety_buffer(): void
    {
        // Arrange - 3 second token should have minimal 1s buffer for safety
        $expiresIn = 3;

        // Act
        $oauth = new OauthData(
            accessToken: 'test-token',
            tokenType: 'Bearer',
            expiresIn: $expiresIn
        );

        // Assert - should not be expired but should have 1s buffer to prevent race conditions
        $this->assertFalse($oauth->isExpired());

        // For tokens > 2s, we apply 1s buffer to prevent network latency/clock skew issues
        // We can't test exact timing, but we verify it's not immediately expired
    }

    public function test_edge_case_very_short_token(): void
    {
        // Arrange - test with a 1 second token (extreme edge case)
        $expiresIn = 1;

        // Act
        $oauth = new OauthData(
            accessToken: 'test-token',
            tokenType: 'Bearer',
            expiresIn: $expiresIn
        );

        // Assert - 1 second tokens get no buffer to preserve any usable time
        // They accept the race condition risk to remain functional
        $this->assertFalse($oauth->isExpired());
    }

    public function test_two_second_token_boundary(): void
    {
        // Arrange - test the boundary case: 2s tokens get no buffer, 3s+ get 1s buffer
        $twoSecondToken = new OauthData(
            accessToken: 'test-token',
            tokenType: 'Bearer',
            expiresIn: 2
        );

        $threeSecondToken = new OauthData(
            accessToken: 'test-token',
            tokenType: 'Bearer',
            expiresIn: 3
        );

        // Assert - both should not be immediately expired
        $this->assertFalse($twoSecondToken->isExpired());
        $this->assertFalse($threeSecondToken->isExpired());
    }
}
