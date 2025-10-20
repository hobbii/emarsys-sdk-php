<?php

declare(strict_types=1);

/**
 * Example usage of the Emarsys SDK.
 *
 * This file demonstrates how to use the Emarsys SDK to manage contact lists
 * using OAuth 2.0 authentication.
 * Replace the credentials with your actual Emarsys OAuth 2.0 credentials.
 */

require_once __DIR__.'/vendor/autoload.php';

use Hobbii\Emarsys\Client;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;
use Hobbii\Emarsys\DTO\CreateContactListRequest;

// Initialize the Emarsys client with OAuth 2.0 credentials
$client = new Client(
    clientId: 'your-client-id',         // Replace with your Emarsys client ID
    clientSecret: 'your-client-secret'  // Replace with your Emarsys client secret
);

try {
    // Example 1: Create a new contact list
    echo "Creating a new contact list...\n";

    $request = new CreateContactListRequest(
        name: 'SDK Test List '.date('Y-m-d H:i:s'),
        description: 'A test contact list created using the Emarsys SDK',
        type: 'static'
    );

    $newContactList = $client->contactLists()->create($request);
    echo "✅ Created contact list: {$newContactList->name} (ID: {$newContactList->id})\n\n";

    // Example 2: List all contact lists
    echo "Fetching all contact lists...\n";

    $collection = $client->contactLists()->list();

    if ($collection->isEmpty()) {
        echo "No contact lists found.\n";
    } else {
        echo "Found {$collection->count()} contact lists:\n";

        foreach ($collection->getContactLists() as $contactList) {
            echo "  - ID: {$contactList->id}, Name: {$contactList->name}\n";
        }
    }
    echo "\n";

    // Example 3: Get a specific contact list
    echo "Getting contact list details...\n";

    $contactListDetails = $client->contactLists()->get($newContactList->id);
    echo "✅ Contact List Details:\n";
    echo "  Name: {$contactListDetails->name}\n";
    echo '  Description: '.($contactListDetails->description ?? 'None')."\n";
    echo '  Type: '.($contactListDetails->type ?? 'Unknown')."\n";
    echo '  Created: '.($contactListDetails->created ?? 'Unknown')."\n";
    echo '  Count: '.($contactListDetails->count ?? 0)." contacts\n\n";

    // Example 4: Delete the contact list we created
    echo "Deleting the test contact list...\n";

    $deleted = $client->contactLists()->delete($newContactList->id);

    if ($deleted) {
        echo "✅ Contact list deleted successfully.\n";
    }

} catch (AuthenticationException $e) {
    echo "❌ OAuth authentication error: {$e->getMessage()}\n";
    echo "   HTTP Status: {$e->getHttpStatusCode()}\n";
    echo "   Please check your client_id and client_secret.\n";

} catch (ApiException $e) {
    echo "❌ API error: {$e->getMessage()}\n";
    echo "   HTTP Status: {$e->getHttpStatusCode()}\n";

    if ($e->getResponseBody()) {
        echo '   Response: '.json_encode($e->getResponseBody(), JSON_PRETTY_PRINT)."\n";
    }

} catch (Exception $e) {
    echo "❌ Unexpected error: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";
}

echo "\nExample completed.\n";
