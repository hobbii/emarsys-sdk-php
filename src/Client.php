<?php

declare(strict_types=1);

namespace Hobbii\Emarsys;

use Hobbii\Emarsys\Domain\BaseClient;
use Hobbii\Emarsys\Domain\Contact\ContactClient;
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

    private ?ContactClient $contact = null;

    private ?ContactListsClient $contactLists = null;

    public function __construct(
        string $clientId,
        string $clientSecret,
    ) {
        $oauthClient = new OauthClient($clientId, $clientSecret);
        $this->client = new BaseClient($oauthClient);
    }

    /**
     * Get the Contact client.
     */
    public function contact(): ContactClient
    {
        return $this->contact ??= new ContactClient($this->client);
    }

    /**
     * Get the Contact Lists client.
     */
    public function contactLists(): ContactListsClient
    {
        return $this->contactLists ??= new ContactListsClient($this->client);
    }
}
