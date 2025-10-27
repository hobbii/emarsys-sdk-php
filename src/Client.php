<?php

declare(strict_types=1);

namespace Hobbii\Emarsys;

use Hobbii\Emarsys\Domain\Client as EmarsysClient;
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
    private readonly EmarsysClient $client;

    private ?ContactListsClient $contactLists = null;

    public function __construct(
        string $clientId,
        string $clientSecret,
    ) {
        $oauthClient = new OauthClient($clientId, $clientSecret);
        $this->client = new EmarsysClient($oauthClient);
    }

    /**
     * Get the Contact Lists client.
     */
    public function contactLists(): ContactListsClient
    {
        return $this->contactLists ??= new ContactListsClient($this->client);
    }
}
