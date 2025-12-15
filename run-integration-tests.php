<?php

declare(strict_types=1);

/**
 * Integration Test Runner for Emarsys SDK
 *
 * This script provides an easy way to run integration tests with real Emarsys credentials.
 */

require_once __DIR__.'/vendor/autoload.php';

use Hobbii\Emarsys\Tests\Integration\QuickConnectionTest;
use Hobbii\Emarsys\Tests\Integration\Runner;
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

$runner = new Runner([
    'quick' => QuickConnectionTest::class,
], $clientId, $clientSecret);

exit($runner->run($argv));
