<?php

declare(strict_types=1);

/**
 * Integration Test Runner for Emarsys SDK
 *
 * This script provides an easy way to run integration tests with real Emarsys credentials.
 *
 * Usage:
 *   php run-integration-tests.php [test-name]
 *
 * Available tests:
 *   - quick       : Quick connection test (read-only)
 *   - contact-lists : Full contact lists CRUD test
 *   - all         : Run all integration tests (default)
 */

require_once __DIR__.'/vendor/autoload.php';

use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;
use Hobbii\Emarsys\Tests\Integration\ContactListsIntegrationTest;
use Hobbii\Emarsys\Tests\Integration\QuickConnectionTest;

// Load .env file if it exists
if (file_exists(__DIR__.'/.env')) {
    $envLines = file(__DIR__.'/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue; // Skip comments and invalid lines
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'"); // Remove quotes and whitespace
        if (! empty($key) && ! isset($_ENV[$key])) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Check for credentials
$clientId = $_ENV['EMARSYS_CLIENT_ID'] ?? null;
$clientSecret = $_ENV['EMARSYS_CLIENT_SECRET'] ?? null;

if (! $clientId || ! $clientSecret) {
    echo "🚨 Emarsys credentials not found!\n\n";
    echo "Please set your credentials first:\n";
    echo "export EMARSYS_CLIENT_ID='your-client-id'\n";
    echo "export EMARSYS_CLIENT_SECRET='your-client-secret'\n\n";
    echo "Or create a .env file based on .env.example\n\n";
    exit(1);
}

$testName = $argv[1] ?? null;

if ($testName === null) {
    echoUsageInfo();
    exit(0);
}

$availableTests = [
    'quick' => QuickConnectionTest::class,
    'contact-lists' => ContactListsIntegrationTest::class,
];

try {
    echo "🧪 Emarsys SDK Integration Test Runner\n";
    echo "=====================================\n\n";

    $tests = getTests($testName);

    foreach ($tests as $test) {
        echo 'Running Test: '.get_class($test)."...\n\n";
        $test->run();
        echo "\nDone.\n";
    }
} catch (AuthenticationException $e) {
    echo "❌ Authentication failed\n";
    echo "💡 Please check your client_id and client_secret.\n";
    echoExceptionDetails($e);
} catch (ApiException $e) {
    echo "❌ API error\n";
    echoExceptionDetails($e);
} catch (Throwable $e) {
    echoExceptionDetails($e);
}

function getTests(string $testName): array
{
    global $availableTests;

    if ($testName === 'all') {
        $test = array_values($availableTests);
    } else {
        $availableTestNames = array_keys($availableTests);

        if (! in_array($testName, $availableTestNames)) {
            echo "❌ Unknown test: {$testName}\n\n";
            echoUsageInfo();
            exit(0);
        }

        $test = $availableTests[$testName];
    }

    return is_array($test) ? array_map(fn ($t) => new $t, $test) : [new $test];
}

function echoUsageInfo(): void
{
    echo "Available tests:\n";
    echo "  - quick         : Quick connection test (read-only)\n";
    echo "  - contact-lists : Full contact lists CRUD test\n";
    echo "  - all           : Run all integration tests (default)\n\n";
    echo "Usage: php run-integration-tests.php [test-name]\n\n";
}

function echoExceptionDetails(Throwable $e): void
{
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Stack Trace:\n".$e->getTraceAsString();
    echo "\n";

    if ($e->getPrevious() !== null) {
        echo "\nCaused by:\n";
        echoExceptionDetails($e->getPrevious());
    }
}
