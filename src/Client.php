<?php

declare(strict_types=1);

namespace Hobbii\Emarsys;

use Hobbii\Emarsys\Domain\BaseClient;
use Hobbii\Emarsys\Domain\Contact\ContactsClient;
use Hobbii\Emarsys\Domain\ContactLists\ContactListsClient;
use Hobbii\Emarsys\Domain\OauthClient;

/**
 * Main Emarsys API client.
 *
 * This class provides access to various Emarsys API endpoints through
 * specialized client instances.
 */
class Client
{
    private readonly BaseClient $client;

    private ?ContactsClient $contactsClient = null;

    private ?ContactListsClient $contactListsClient = null;

    public function __construct(
        string $clientId,
        string $clientSecret,
    ) {
        $oauthClient = new OauthClient($clientId, $clientSecret);
        $this->client = new BaseClient($oauthClient);
    }

    /**
     * Get the Contacts client.
     */
    public function contacts(): ContactsClient
    {
        return $this->contactsClient ??= new ContactsClient($this->client);
    }

    /**
     * Get the Contact Lists client.
     */
    public function contactLists(): ContactListsClient
    {
        return $this->contactListsClient ??= new ContactListsClient($this->client);
    }
}
