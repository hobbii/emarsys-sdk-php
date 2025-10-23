<?php

declare(strict_types=1);

namespace Hobbii\Emarsys;

use Hobbii\Emarsys\Domain\Client as EmarsysClient;
use Hobbii\Emarsys\Domain\ContactLists\ContactListsClient;

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
        ?string $baseUrl = null
    ) {
        $this->client = new EmarsysClient($clientId, $clientSecret, $baseUrl);
    }

    /**
     * Get the Contact Lists client.
     */
    public function contactLists(): ContactListsClient
    {
        return $this->contactLists ??= new ContactListsClient($this->client);
    }
}
