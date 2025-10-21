<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain;

use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;
use Hobbii\Emarsys\DTO\ContactList;
use Hobbii\Emarsys\DTO\ContactListCollection;
use Hobbii\Emarsys\DTO\CreateContactListRequest;

/**
 * Client for managing Emarsys Contact Lists.
 */
class ContactListsClient
{
    private const ENDPOINT = 'contactlist';

    public function __construct(
        private readonly HttpClient $httpClient
    ) {}

    /**
     * Create a new contact list.
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function create(CreateContactListRequest $request): ContactList
    {
        $response = $this->httpClient->post(self::ENDPOINT, $request->toArray());

        if (! isset($response['data'])) {
            throw new ApiException('Invalid response format: missing data field');
        }

        return ContactList::fromArray($response['data']);
    }

    /**
     * List all contact lists.
     *
     * @param  array<string, mixed>  $filters  Optional filters for the request
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function list(array $filters = []): ContactListCollection
    {
        $response = $this->httpClient->get(self::ENDPOINT, $filters);

        return ContactListCollection::fromArray($response);
    }

    /**
     * Get a specific contact list by ID.
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function get(int $contactListId): ContactList
    {
        $response = $this->httpClient->get(self::ENDPOINT . '/' . $contactListId);

        if (! isset($response['data'])) {
            throw new ApiException('Invalid response format: missing data field');
        }

        return ContactList::fromArray($response['data']);
    }

    /**
     * Delete a contact list by ID.
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function delete(int $contactListId): bool
    {
        $this->httpClient->delete(self::ENDPOINT . '/' . $contactListId);

        return true;
    }
}
