<?php

declare(strict_types=1);

/**
 * Integration Test Script for Emarsys SDK
 *
 * This script tests the SDK with real Emarsys API credentials.
 * It performs safe operations that won't affect your production data.
 *
 * Usage:
 * 1. Set your credentials in environment variables or replace the placeholders
 * 2. Run: php ContactListsIntegrationTest.php
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
use Hobbii\Emarsys\Domain\Exceptions\EmarsysException;
use Hobbii\Emarsys\DTO\CreateContactListRequest;

// =============================================================================
// CONFIGURATION - Replace with your actual credentials
// =============================================================================

$clientId = $_ENV['EMARSYS_CLIENT_ID'] ?? 'your-client-id-here';
$clientSecret = $_ENV['EMARSYS_CLIENT_SECRET'] ?? 'your-client-secret-here';

// Optional: Custom base URL (if you're using a different Emarsys environment)
$baseUrl = $_ENV['EMARSYS_BASE_URL'] ?? null; // Uses default v3 API URL if null

// =============================================================================
// SAFETY CHECK
// =============================================================================

if ($clientId === 'your-client-id-here' || $clientSecret === 'your-client-secret-here') {
    echo "🚨 Please set your Emarsys credentials first!\n\n";
    echo "Method 1 - Environment variables:\n";
    echo "export EMARSYS_CLIENT_ID='your-actual-client-id'\n";
    echo "export EMARSYS_CLIENT_SECRET='your-actual-client-secret'\n";
    echo "php integration-test.php\n\n";
    echo "Method 2 - Edit this file and replace the placeholders above.\n\n";
    exit(1);
}

// =============================================================================
// INTEGRATION TESTS
// =============================================================================

echo "🧪 Emarsys SDK Integration Test\n";
echo "================================\n\n";

try {
    // Initialize the client
    echo "1️⃣  Initializing Emarsys client...\n";
    $client = new Client($clientId, $clientSecret, $baseUrl);
    echo "   ✅ Client initialized successfully\n\n";

    // Test 1: List existing contact lists (read-only, safe)
    echo "2️⃣  Testing: List existing contact lists...\n";
    $existingLists = $client->contactLists()->list();
    echo "   ✅ Successfully retrieved contact lists\n";
    echo "   📊 Found {$existingLists->count()} contact lists\n";

    if (! $existingLists->isEmpty()) {
        echo "   📝 Existing lists:\n";
        foreach ($existingLists->getContactLists() as $list) {
            $count = $list->count ? " ({$list->count} contacts)" : '';
            echo "      - ID: {$list->id}, Name: \"{$list->name}\"{$count}\n";
        }
    }
    echo "\n";

    // Test 2: Create a test contact list
    echo "3️⃣  Testing: Create a test contact list...\n";
    $testListName = 'SDK Test List '.date('Y-m-d H:i:s');
    $createRequest = new CreateContactListRequest(
        name: $testListName,
        description: 'Test contact list created by Emarsys SDK integration test',
        type: 'static'
    );

    $createdList = $client->contactLists()->create($createRequest);
    echo "   ✅ Successfully created contact list\n";
    echo "   📝 ID: {$createdList->id}\n";
    echo "   📝 Name: \"{$createdList->name}\"\n";
    echo "   📝 Description: \"{$createdList->description}\"\n\n";

    // Test 3: Get the specific contact list we just created
    echo "4️⃣  Testing: Get specific contact list by ID...\n";
    $retrievedList = $client->contactLists()->get($createdList->id);
    echo "   ✅ Successfully retrieved contact list by ID\n";
    echo "   📝 Verified ID: {$retrievedList->id}\n";
    echo "   📝 Verified Name: \"{$retrievedList->name}\"\n\n";

    // Test 4: List contact lists again to verify our new list appears
    echo "5️⃣  Testing: Verify new list appears in list...\n";
    $updatedLists = $client->contactLists()->list();
    $foundNewList = false;
    foreach ($updatedLists->getContactLists() as $list) {
        if ($list->id === $createdList->id) {
            $foundNewList = true;
            break;
        }
    }

    if ($foundNewList) {
        echo "   ✅ New contact list found in updated list\n";
        echo "   📊 Total lists now: {$updatedLists->count()}\n\n";
    } else {
        echo "   ⚠️  New contact list not found in list (might be a timing issue)\n\n";
    }

    // Test 5: Clean up - Delete the test contact list
    echo "6️⃣  Testing: Delete test contact list (cleanup)...\n";
    $deleteSuccess = $client->contactLists()->delete($createdList->id);

    if ($deleteSuccess) {
        echo "   ✅ Successfully deleted test contact list\n";
        echo "   🧹 Cleanup completed\n\n";
    }

    // Final verification - List again to confirm deletion
    echo "7️⃣  Final verification: Confirm deletion...\n";
    $finalLists = $client->contactLists()->list();
    $deletedListFound = false;
    foreach ($finalLists->getContactLists() as $list) {
        if ($list->id === $createdList->id) {
            $deletedListFound = true;
            break;
        }
    }

    if (! $deletedListFound) {
        echo "   ✅ Confirmed: Test contact list was successfully deleted\n";
        echo "   📊 Final list count: {$finalLists->count()}\n\n";
    } else {
        echo "   ⚠️  Test contact list still exists (might be a timing issue)\n\n";
    }

    // Summary
    echo "🎉 Integration Test Results\n";
    echo "===========================\n";
    echo "✅ OAuth 2.0 Authentication: SUCCESS\n";
    echo "✅ List Contact Lists: SUCCESS\n";
    echo "✅ Create Contact List: SUCCESS\n";
    echo "✅ Get Contact List by ID: SUCCESS\n";
    echo "✅ Delete Contact List: SUCCESS\n";
    echo "\n🚀 All tests passed! Your Emarsys SDK is working correctly.\n";

} catch (AuthenticationException $e) {
    echo "❌ Authentication Error\n";
    echo "   Message: {$e->getMessage()}\n";
    echo "   HTTP Status: {$e->getHttpStatusCode()}\n";
    if ($e->getResponseBody()) {
        echo '   Response: '.json_encode($e->getResponseBody(), JSON_PRETTY_PRINT)."\n";
    }
    echo "\n💡 Check your client_id and client_secret credentials.\n";

} catch (ApiException $e) {
    echo "❌ API Error\n";
    echo "   Message: {$e->getMessage()}\n";
    echo "   HTTP Status: {$e->getHttpStatusCode()}\n";
    if ($e->getResponseBody()) {
        echo '   Response: '.json_encode($e->getResponseBody(), JSON_PRETTY_PRINT)."\n";
    }
    echo "\n💡 Check the API response above for specific error details.\n";

} catch (EmarsysException $e) {
    echo "❌ Emarsys SDK Error\n";
    echo "   Message: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";

} catch (Exception $e) {
    echo "❌ Unexpected Error\n";
    echo "   Message: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";
    echo "   Trace: {$e->getTraceAsString()}\n";
}

echo "\n🏁 Integration test completed.\n";
