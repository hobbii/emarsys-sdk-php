<?php

declare(strict_types=1);

/**
 * Quick Test Script for Emarsys SDK
 *
 * A simple script to quickly test authentication and basic API connectivity.
 * This only performs read operations (safe for production).
 */

require_once __DIR__.'/../../vendor/autoload.php';

// Load .env file if it exists
if (file_exists(__DIR__.'/../../.env')) {
    $envLines = file(__DIR__.'/../../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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

use Hobbii\Emarsys\Client;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;

// Set your credentials here or via environment variables
$clientId = $_ENV['EMARSYS_CLIENT_ID'] ?? 'your-client-id-here';
$clientSecret = $_ENV['EMARSYS_CLIENT_SECRET'] ?? 'your-client-secret-here';

if ($clientId === 'your-client-id-here' || $clientSecret === 'your-client-secret-here') {
    echo "Please set your credentials first:\n";
    echo "export EMARSYS_CLIENT_ID='your-client-id'\n";
    echo "export EMARSYS_CLIENT_SECRET='your-client-secret'\n";
    exit(1);
}

echo "Testing Emarsys SDK...\n\n";

try {
    $client = new Client($clientId, $clientSecret);

    echo "✅ Client created successfully\n";
    echo "🔍 Testing API connection by listing contact lists...\n\n";

    $lists = $client->contactLists()->list();

    echo "🎉 SUCCESS! API connection working.\n";
    echo "📊 Found {$lists->count()} contact lists in your Emarsys account.\n\n";

    if (! $lists->isEmpty()) {
        echo "📝 Your contact lists:\n";
        foreach ($lists->getContactLists() as $list) {
            $count = $list->count ? " ({$list->count} contacts)" : '';
            echo "   - {$list->name} (ID: {$list->id}){$count}\n";
        }
    }

} catch (AuthenticationException $e) {
    echo "❌ Authentication failed: {$e->getMessage()}\n";
    echo "💡 Please check your client_id and client_secret.\n";

} catch (ApiException $e) {
    echo "❌ API error: {$e->getMessage()}\n";
    echo "HTTP Status: {$e->getHttpStatusCode()}\n";

} catch (Exception $e) {
    echo "❌ Unexpected error: {$e->getMessage()}\n";
}

echo "\nDone.\n";
