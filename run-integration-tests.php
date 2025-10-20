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
        if (!empty($key) && !isset($_ENV[$key])) {
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

$testName = $argv[1] ?? 'all';
$testDir = __DIR__.'/tests/Integration';

echo "🧪 Emarsys SDK Integration Test Runner\n";
echo "=====================================\n\n";

switch ($testName) {
    case 'quick':
        echo "Running Quick Connection Test...\n\n";
        require $testDir.'/QuickConnectionTest.php';
        break;

    case 'contact-lists':
        echo "Running Contact Lists Integration Test...\n\n";
        require $testDir.'/ContactListsIntegrationTest.php';
        break;

    case 'all':
        echo "Running All Integration Tests...\n\n";

        echo "1. Quick Connection Test\n";
        echo "========================\n";
        require $testDir.'/QuickConnectionTest.php';

        echo "\n\n2. Contact Lists Integration Test\n";
        echo "==================================\n";
        require $testDir.'/ContactListsIntegrationTest.php';
        break;

    default:
        echo "❌ Unknown test: {$testName}\n\n";
        echo "Available tests:\n";
        echo "  - quick       : Quick connection test (read-only)\n";
        echo "  - contact-lists : Full contact lists CRUD test\n";
        echo "  - all         : Run all integration tests (default)\n\n";
        echo "Usage: php run-integration-tests.php [test-name]\n";
        exit(1);
}

echo "\n🏁 Integration tests completed.\n";
