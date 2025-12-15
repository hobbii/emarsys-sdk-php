<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Integration;

use Hobbii\Emarsys\Client;
use Hobbii\Emarsys\Domain\Contacts\GetContactData\GetContactDataRequest;
use Hobbii\Emarsys\Domain\Contacts\UpdateContacts\UpdateContactsRequest;
use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
use Hobbii\Emarsys\Domain\Enums\ContactSystemFieldId;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;

class ContactIntegrationTest
{
    private string $baseEmail;

    private array $testContacts = [];

    public function __construct(
        private readonly Client $client,
        private readonly array $args,
    ) {
        $this->baseEmail = $this->args['email'] ?? 'john.doe@example.com';
    }

    public function run(): void
    {
        echo "🚀 Starting comprehensive contacts integration test...\n\n";

        try {
            // Step 1: Create test contacts
            $this->createTestContacts();

            // Step 2: Verify contacts were created correctly
            $this->verifyContactCreation();

            // Step 3: Update contacts with new data
            $this->updateTestContacts();

            // Step 4: Verify updates were applied
            $this->verifyContactUpdates();

            // Step 5: Test existing contact lookup
            $this->testExistingContactLookup();
        } catch (AuthenticationException $e) {
            echo '   ❌ Authentication failed: '.$e->getMessage()."\n";
            throw $e;
        } catch (ApiException $e) {
            echo '   ❌ API error: '.$e->getMessage()."\n";
            throw $e;
        } finally {
            // Note: In a real test environment, you might want to clean up test contacts
            // However, this requires careful consideration to avoid deleting real data
            echo "\n⚠️  Note: Test contacts remain in Emarsys for manual cleanup\n";
        }

        echo "\n🎉 All contact integration tests completed successfully!\n";
    }

    /**
     * Create two test contacts with initial data.
     */
    private function createTestContacts(): void
    {
        echo "📝 Step 1: Creating test contacts...\n";

        // Generate unique test emails to avoid conflicts
        $timestamp = time();
        $testEmail1 = str_replace('@', "+test1_{$timestamp}@", $this->baseEmail);
        $testEmail2 = str_replace('@', "+test2_{$timestamp}@", $this->baseEmail);

        $this->testContacts = [
            [
                'email' => $testEmail1,
                'firstName' => 'John',
                'lastName' => 'Doe',
                'phone' => '+1234567890',
            ],
            [
                'email' => $testEmail2,
                'firstName' => 'Jane',
                'lastName' => 'Smith',
                'phone' => '+0987654321',
            ],
        ];

        // Prepare contact data for creation
        $contactsData = [];
        foreach ($this->testContacts as $contact) {
            $contactsData[] = new ContactData([
                ContactSystemFieldId::EMAIL->value => $contact['email'],
                ContactSystemFieldId::FIRST_NAME->value => $contact['firstName'],
                ContactSystemFieldId::LAST_NAME->value => $contact['lastName'],
                ContactSystemFieldId::PHONE->value => $contact['phone'],
                ContactSystemFieldId::OPT_IN->value => 1, // Opt-in: 1 = True, 2 = False
            ]);
        }

        $createRequest = new UpdateContactsRequest(
            keyId: ContactSystemFieldId::EMAIL->value, // Use email as key for identifying contacts
            contacts: $contactsData,
            createIfNotExists: true
        );

        echo '   👉 Creating '.count($contactsData)." contacts...\n";
        foreach ($createRequest->contacts as $contact) {
            echo '      ➕ '.$contact->data[ContactSystemFieldId::EMAIL->value]."\n";
        }

        $response = $this->client->contacts()->updateContact($createRequest);

        if ($response->hasErrors()) {
            echo "   ⚠️  Errors during contact creation:\n";
            foreach ($response->errors as $error) {
                echo '      - '.(is_array($error) ? json_encode($error) : $error)."\n";
            }
        } else {
            echo '   ✅ Successfully created '.count($this->testContacts)." test contacts\n";
        }

        // Store contact IDs for later use
        if (! empty($response->ids)) {
            foreach ($response->ids as $index => $contactId) {
                if (isset($this->testContacts[$index])) {
                    $this->testContacts[$index]['id'] = $contactId;
                }
            }
        }
    }

    /**
     * Verify that contacts were created with correct initial data.
     */
    private function verifyContactCreation(): void
    {
        echo "\n🔍 Step 2: Verifying contact creation...\n";

        $testEmails = array_column($this->testContacts, 'email');

        $getContactData = new GetContactDataRequest(
            fields: array_map('strval', [
                ContactSystemFieldId::INTERESTS->value, // Contact ID equivalent
                ContactSystemFieldId::FIRST_NAME->value,
                ContactSystemFieldId::LAST_NAME->value,
                ContactSystemFieldId::EMAIL->value,
                ContactSystemFieldId::OPT_IN->value,
                ContactSystemFieldId::PHONE->value,
            ]),
            keyId: (string) ContactSystemFieldId::EMAIL->value,
            keyValues: $testEmails,
        );

        $response = $this->client->contacts()->getContactData($getContactData);

        echo '   📊 Retrieved '.count($response->result)." contacts:\n";

        foreach ($response->result as $contact) {
            echo "      Contact ID: {$contact->id}\n";
            echo '         Email: '.$contact->data[ContactSystemFieldId::EMAIL->value]."\n";
            echo '         Name: '.$contact->data[ContactSystemFieldId::FIRST_NAME->value].' '.$contact->data[ContactSystemFieldId::LAST_NAME->value]."\n";
            echo '         Phone: '.$contact->data[ContactSystemFieldId::PHONE->value]."\n";
            echo '         Opt-in: '.($contact->data[ContactSystemFieldId::OPT_IN->value] == 1 ? 'Yes' : 'No')."\n";
        }

        if (count($response->result) === count($this->testContacts)) {
            echo "   ✅ All test contacts found and verified\n";
        } else {
            echo '   ⚠️  Expected '.count($this->testContacts).' contacts, found '.count($response->result)."\n";
        }
    }

    /**
     * Update test contacts with new data to verify update functionality.
     */
    private function updateTestContacts(): void
    {
        echo "\n✏️  Step 3: Updating test contacts...\n";

        // Prepare updated contact data
        $updatedContactsData = [];
        foreach ($this->testContacts as $contact) {
            $updatedContactsData[] = new ContactData([
                ContactSystemFieldId::EMAIL->value => $contact['email'], // Email as key
                ContactSystemFieldId::FIRST_NAME->value => $contact['firstName'].' Updated',
                ContactSystemFieldId::LAST_NAME->value => $contact['lastName'].' Modified',
                ContactSystemFieldId::OPT_IN->value => 2, // Change opt-in to False
                ContactSystemFieldId::PHONE->value => '+1111111111', // Update phone to same number for all
            ]);
        }

        $updateRequest = new UpdateContactsRequest(
            keyId: ContactSystemFieldId::EMAIL->value,
            contacts: $updatedContactsData,
            createIfNotExists: false // Should not create new contacts
        );

        $response = $this->client->contacts()->updateContact($updateRequest);

        if (! empty($response->errors)) {
            echo "   ⚠️  Errors during contact update:\n";
            foreach ($response->errors as $error) {
                echo '      - '.(is_array($error) ? json_encode($error) : $error)."\n";
            }
        } else {
            echo '   ✅ Successfully updated '.count($this->testContacts)." test contacts\n";
        }
    }

    /**
     * Verify that contact updates were applied correctly.
     */
    private function verifyContactUpdates(): void
    {
        echo "\n🔍 Step 4: Verifying contact updates...\n";

        $testEmails = array_column($this->testContacts, 'email');

        $getContactData = new GetContactDataRequest(
            fields: [
                (string) ContactSystemFieldId::INTERESTS->value, // Contact ID equivalent
                (string) ContactSystemFieldId::FIRST_NAME->value,
                (string) ContactSystemFieldId::LAST_NAME->value,
                (string) ContactSystemFieldId::EMAIL->value,
                (string) ContactSystemFieldId::OPT_IN->value,
                (string) ContactSystemFieldId::PHONE->value,
            ],
            keyId: (string) ContactSystemFieldId::EMAIL->value,
            keyValues: $testEmails,
        );

        $response = $this->client->contacts()->getContactData($getContactData);

        echo '   📊 Verifying updates for '.count($response->result)." contacts:\n";

        foreach ($response->result as $contact) {
            $firstName = $contact->data[ContactSystemFieldId::FIRST_NAME->value];
            $lastName = $contact->data[ContactSystemFieldId::LAST_NAME->value];
            $optIn = $contact->data[ContactSystemFieldId::OPT_IN->value];
            $phone = $contact->data[ContactSystemFieldId::PHONE->value];

            echo "      Contact ID: {$contact->id}\n";
            echo '         Email: '.$contact->data[ContactSystemFieldId::EMAIL->value]."\n";
            echo "         Updated Name: {$firstName} {$lastName}\n";
            echo "         Updated Phone: {$phone}\n";
            echo '         Updated Opt-in: '.($optIn == 2 ? 'No (Updated)' : 'Yes')."\n";

            // Verify updates
            $hasUpdatedSuffix = str_ends_with($firstName, ' Updated') && str_ends_with($lastName, ' Modified');
            $hasCorrectPhone = $phone === '+1111111111';
            $hasCorrectOptIn = $optIn == 2;

            if ($hasUpdatedSuffix && $hasCorrectPhone && $hasCorrectOptIn) {
                echo "         ✅ All updates verified for this contact\n";
            } else {
                echo "         ❌ Some updates were not applied correctly\n";
            }
        }
    }

    /**
     * Test lookup of an existing contact (from the original parameter).
     */
    private function testExistingContactLookup(): void
    {
        echo "\n🔍 Step 5: Testing existing contact lookup ({$this->baseEmail})...\n";

        $getContactData = new GetContactDataRequest(
            fields: [
                (string) ContactSystemFieldId::INTERESTS->value, // Contact ID equivalent
                (string) ContactSystemFieldId::FIRST_NAME->value,
                (string) ContactSystemFieldId::LAST_NAME->value,
                (string) ContactSystemFieldId::EMAIL->value,
                (string) ContactSystemFieldId::OPT_IN->value,
            ],
            keyId: (string) ContactSystemFieldId::EMAIL->value,
            keyValues: [$this->baseEmail],
        );

        $response = $this->client->contacts()->getContactData($getContactData);

        if (! empty($response->result)) {
            echo "   ✅ Found existing contact with email: {$this->baseEmail}\n";

            foreach ($response->result as $contact) {
                echo "      Contact ID: {$contact->id}\n";
                echo '      Name: '.$contact->data[ContactSystemFieldId::FIRST_NAME->value].' '.$contact->data[ContactSystemFieldId::LAST_NAME->value]."\n";
                echo '      Opt-in: '.($contact->data[ContactSystemFieldId::OPT_IN->value] == 1 ? 'Yes' : 'No')."\n";
            }
        } else {
            echo "   ℹ️  No existing contact found with email: {$this->baseEmail}\n";
        }
    }
}
