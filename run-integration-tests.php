<?php

declare(strict_types=1);

/**
 * Integration Test Runner for Emarsys SDK
 *
 * This script provides an easy way to run integration tests with real Emarsys credentials.
 */

require_once __DIR__.'/vendor/autoload.php';

use Hobbii\Emarsys\Client;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;
use Hobbii\Emarsys\Tests\Integration\ContactIntegrationTest;
use Hobbii\Emarsys\Tests\Integration\ContactListsIntegrationTest;
use Hobbii\Emarsys\Tests\Integration\QuickConnectionTest;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv;
$dotenv->bootEnv(__DIR__.'/.env');

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

// Parse command line arguments
$parsedArgs = parseArguments($argv);
$testName = $parsedArgs['test'] ?? null;

if ($testName === null) {
    echoUsageInfo();
    exit(0);
}

$availableTests = [
    'quick' => QuickConnectionTest::class,
    'contact-lists' => ContactListsIntegrationTest::class,
    'contact' => ContactIntegrationTest::class,
];

try {
    echo "🧪 Emarsys SDK Integration Test Runner\n";
    echo "=====================================\n\n";

    $client = new Client($clientId, $clientSecret);

    echo "✅ Client created successfully\n";

    $tests = createTests($testName, $availableTests, $client, $parsedArgs);

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

function createTests(string $testName, array $availableTests, Client $client, array $args): array
{
    if ($testName === 'all') {
        $tests = array_values($availableTests);
    } else {
        $availableTestNames = array_keys($availableTests);

        if (! in_array($testName, $availableTestNames)) {
            echo "❌ Unknown test: {$testName}\n\n";
            echoUsageInfo();
            exit(0);
        }

        $tests = [$availableTests[$testName]];
    }

    return array_map(function ($testClass) use ($client, $args) {
        // Pass email parameter to ContactIntegrationTest
        if ($testClass === ContactIntegrationTest::class) {
            return new $testClass($client, $args);
        }

        return new $testClass($client);
    }, $tests);
}

function parseArguments(array $argv): array
{
    $result = [];

    // First argument (after script name) is the test name
    if (isset($argv[1]) && ! str_contains($argv[1], '=')) {
        $result['test'] = $argv[1];
        $startIndex = 2;
    } else {
        $startIndex = 1;
    }

    // Parse key=value parameters
    for ($i = $startIndex; $i < count($argv); $i++) {
        if (str_contains($argv[$i], '=')) {
            [$key, $value] = explode('=', $argv[$i], 2);
            $result[$key] = $value;
        } elseif (! isset($result['test'])) {
            // If no test specified yet, treat as test name
            $result['test'] = $argv[$i];
        }
    }

    return $result;
}

function echoUsageInfo(): void
{
    echo "Available tests:\n";
    echo "  - quick         : Quick connection test (read-only)\n";
    echo "  - contact-lists : Full contact lists CRUD test\n";
    echo "  - contact       : Contact data retrieval test\n";
    echo "  - all           : Run all integration tests (default)\n\n";
    echo "Usage:\n";
    echo "  php run-integration-tests.php [test-name] [email=user@example.com]\n";
    echo "  php run-integration-tests.php contact email=john@doe.com\n\n";
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
