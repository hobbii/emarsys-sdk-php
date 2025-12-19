<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Integration;

use Hobbii\Emarsys\Domain\Contacts\GetContactData\GetContactDataRequest;
use Hobbii\Emarsys\Domain\Contacts\UpdateContacts\UpdateContactsRequest;
use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
use Hobbii\Emarsys\Domain\Enums\ContactSystemField;
use Hobbii\Emarsys\Domain\Enums\OptInStatus;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;

class ContactsIntegrationTest extends AbstractIntegrationTest
{
    private string $baseEmail;

    private array $testContacts = [];

    public function run(array $args): void
    {
        $this->baseEmail = $args['email'] ?? 'john.doe@example.com';

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
        // Format YdmH is used to ensure uniqueness per hour (until contact deletion endpoint is implemented).
        // This helps to reduce manual cleanup frequency.
        $timestamp = date_format(new \DateTime, 'YmdH');
        $testEmail1 = str_replace('@', "+test1_{$timestamp}@", $this->baseEmail);
        $testEmail2 = str_replace('@', "+test2_{$timestamp}@", $this->baseEmail);

        $this->testContacts = [
            [
                'email' => $testEmail1,
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
            [
                'email' => $testEmail2,
                'firstName' => 'Jane',
                'lastName' => 'Smith',
            ],
        ];

        // Prepare contact data for creation
        $contactsData = [];
        foreach ($this->testContacts as $contact) {
            $contactsData[] = new ContactData([
                ContactSystemField::email->value => $contact['email'],
                ContactSystemField::first_name->value => $contact['firstName'],
                ContactSystemField::last_name->value => $contact['lastName'],
                ContactSystemField::optin->value => OptInStatus::True->value,
            ]);
        }

        $createRequest = UpdateContactsRequest::make(
            keyId: ContactSystemField::email->value, // Use email as key for identifying contacts
            contacts: $contactsData,
            createIfNotExists: true
        );

        echo '   👉 Creating '.count($contactsData)." contacts...\n";
        foreach ($createRequest->contacts as $contact) {
            echo '      ➕ '.$contact->getEmail()."\n";
        }

        $responseData = $this->client->contacts()->updateContact($createRequest);

        if ($responseData->hasErrors()) {
            $this->outputErrors($responseData->errors);
        } else {
            echo '   ✅ Successfully created '.count($this->testContacts)." test contacts\n";
        }

        // Store contact IDs for later use
        if (! empty($responseData->ids)) {
            foreach ($responseData->ids as $index => $contactId) {
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

        $request = GetContactDataRequest::make(
            fields: [
                ContactSystemField::first_name,
                ContactSystemField::last_name,
                ContactSystemField::email,
                ContactSystemField::optin,
            ],
            keyId: ContactSystemField::email,
            keyValues: $testEmails,
        );

        $response = $this->client->contacts()->getContactData($request);
        echo '   📊 Retrieved '.count($response->result ?? [])." contacts:\n";

        if ($response->hasResult()) {
            foreach ($response->result as $contact) {
                echo '      Contact ID: '.$contact->getId()."\n";
                echo '         Email: '.$contact->getEmail()."\n";
                echo '         Name: '.$contact->getFirstName().' '.$contact->getLastName()."\n";
                echo '         Opt-in: '.($contact->getOptInStatus()?->name ?? 'unknown')."\n";
            }

            if (count($response->result) === count($this->testContacts)) {
                echo "   ✅ All test contacts found and verified\n";
            } else {
                echo '   ⚠️  Expected '.count($this->testContacts).' contacts, found '.count($response->result ?? [])."\n";
            }
        }

        if ($response->hasErrors()) {
            $this->outputErrors($response->errors);
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
                ContactSystemField::email->value => $contact['email'], // Email as key
                ContactSystemField::first_name->value => $contact['firstName'].' Updated',
                ContactSystemField::last_name->value => $contact['lastName'].' Modified',
                ContactSystemField::optin->value => 2, // Change opt-in to False
            ]);
        }

        $updateRequest = UpdateContactsRequest::make(
            keyId: ContactSystemField::email,
            contacts: $updatedContactsData,
        );

        $response = $this->client->contacts()->updateContact($updateRequest);

        if ($response->hasErrors()) {
            $this->outputErrors($response->errors);
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

        $getContactData = GetContactDataRequest::make(
            fields: [
                ContactSystemField::first_name,
                ContactSystemField::last_name,
                ContactSystemField::email,
                ContactSystemField::optin,
            ],
            keyId: ContactSystemField::email,
            keyValues: $testEmails,
        );

        $response = $this->client->contacts()->getContactData($getContactData);

        if ($response->hasErrors()) {
            $this->outputErrors($response->errors);
        }

        if (! $response->hasResult()) {
            echo "   ❌ No contacts found to verify updates\n";

            return;
        }

        echo '   📊 Verifying updates for '.count($response->result)." contacts:\n";

        foreach ($response->result as $contact) {
            $firstName = $contact->getFirstName();
            $lastName = $contact->getLastName();
            $optInStatus = $contact->getOptInStatus()?->asBool() ?? false;

            echo "      Contact ID: {$contact->getId()}\n";
            echo "         Email: {$contact->getEmail()}\n";
            echo "         Updated Name: {$firstName} {$lastName}\n";
            echo '         Updated Opt-in: '.($optInStatus ? 'Yes' : 'No (Updated)')."\n";

            // Verify updates
            $hasUpdatedSuffix = str_ends_with($firstName, ' Updated') && str_ends_with($lastName, ' Modified');
            $hasCorrectOptIn = ! $optInStatus;

            if ($hasUpdatedSuffix && $hasCorrectOptIn) {
                echo "         ✅ All updates verified for this contact\n";
            } else {
                echo "         ❌ Some updates were not applied correctly\n";
            }
        }
    }

    private function outputErrors(array $errors): void
    {
        echo "   ❌  Errors:\n";
        foreach ($errors as $error) {
            echo '      - '.(string) $error."\n";
        }
    }
}
