<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Integration;

use Hobbii\Emarsys\Client;
use Hobbii\Emarsys\Domain\Contact\DTOs\GetContactData;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;

class ContactIntegrationTest
{
    private string $email;

    public function __construct(
        private readonly Client $client,
        private readonly array $args,
    ) {
        $this->email = $this->args['email'] ?? throw new \InvalidArgumentException('Email argument is required for contact integration test');
    }

    public function run(): void
    {
        echo "⚒️  Testing: Get contact data by email ({$this->args['email']})...\n";

        try {
            $getContactData = new GetContactData(
                fields: ['1', '2', '3', '4'], // Common fields: ID, First Name, Last Name, Email
                keyId: '3', // Use email field as identifier (field ID 3 is typically email)
                keyValues: [$this->email],
            );

            $contactDataResponse = $this->client->contact()->getData($getContactData);

            echo "   ✅ Successfully retrieved contact data\n";

            // Display results
            if (! empty($contactDataResponse->result)) {
                echo "   📊 Found contact data:\n";

                foreach ($contactDataResponse->result as $index => $contact) {
                    echo '      Contact #'.($index + 1).":\n";
                    echo "         ID: {$contact->id}\n";
                    echo "         UID: {$contact->uid}\n";
                    echo "         Data:\n";

                    foreach ($contact->data as $fieldId => $value) {
                        echo "         Field {$fieldId}: ".(is_array($value) ? json_encode($value) : $value)."\n";
                    }
                }
            } else {
                echo "   ℹ️  No contact found with email '{$this->email}'\n";
            }
        } catch (AuthenticationException $e) {
            echo '   ❌ Authentication failed: '.$e->getMessage()."\n";
            throw $e;
        } catch (ApiException $e) {
            if (! empty($contactDataResponse->errors)) {
                echo "   ⚠️  Errors in response:\n";
                foreach ($contactDataResponse->errors as $error) {
                    echo '      - '.(is_array($error) ? json_encode($error) : $error)."\n";
                }
            }
        } catch (AuthenticationException $e) {
            echo '   ❌ Authentication failed: '.$e->getMessage()."\n";
            throw $e;
        } catch (ApiException $e) {
            echo '   ❌ API error: '.$e->getMessage()."\n";
            throw $e;
        }

        echo "\n";
        echo "🎉 Contact integration tests completed successfully!\n";
    }
}
