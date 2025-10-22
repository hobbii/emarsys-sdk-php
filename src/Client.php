<?php

declare(strict_types=1);

namespace Hobbii\Emarsys;

use Hobbii\Emarsys\Domain\ContactLists\ContactListsClient;
use Hobbii\Emarsys\Domain\HttpClient;

/**
 * Main Emarsys API client.
 *
 * This class provides access to various Emarsys API endpoints through
 * specialized client instances.
 */
class Client
{
    private readonly HttpClient $httpClient;

    private ?ContactListsClient $contactLists = null;

    public function __construct(
        string $clientId,
        string $clientSecret,
        ?string $baseUrl = null
    ) {
        $this->httpClient = new HttpClient($clientId, $clientSecret, $baseUrl);
    }

    /**
     * Get the Contact Lists client.
     */
    public function contactLists(): ContactListsClient
    {
        return $this->contactLists ??= new ContactListsClient($this->httpClient);
    }
}
