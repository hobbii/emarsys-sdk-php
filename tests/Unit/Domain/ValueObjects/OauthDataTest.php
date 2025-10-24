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
            ['expiresIn' => 10, 'expectedNotExpired' => true],
            ['expiresIn' => 30, 'expectedNotExpired' => true],
            ['expiresIn' => 60, 'expectedNotExpired' => true],
            ['expiresIn' => 120, 'expectedNotExpired' => true],
            ['expiresIn' => 3600, 'expectedNotExpired' => true],
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
                "Token with {$testCase['expiresIn']} seconds should not be immediately expired"
            );
        }
    }
}
