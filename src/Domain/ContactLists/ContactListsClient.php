<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ContactLists;

use Hobbii\Emarsys\Domain\ContactLists\DTOs\ContactListCollection;
use Hobbii\Emarsys\Domain\ContactLists\DTOs\CreateContactList;
use Hobbii\Emarsys\Domain\ContactLists\DTOs\CreateContactListResponse;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;
use Hobbii\Emarsys\Domain\HttpClient;

/**
 * Service for managing Emarsys Contact Lists.
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
     *
     * @see https://dev.emarsys.com/docs/core-api-reference/enmevkj1fi016-create-a-contact-list
     */
    public function create(CreateContactList $data): CreateContactListResponse
    {
        $response = $this->httpClient->post(self::ENDPOINT, $data->toArray());

        if ($response->data === null) {
            throw new ApiException('Invalid response format: missing data field');
        }

        return CreateContactListResponse::fromArray($response->data);
    }

    /**
     * List all contact lists.
     *
     * @throws ApiException
     * @throws AuthenticationException
     *
     * @see https://dev.emarsys.com/docs/core-api-reference/axpotjvepqdla-list-contact-lists
     */
    public function list(): ContactListCollection
    {
        $response = $this->httpClient->get(self::ENDPOINT);

        return ContactListCollection::fromArray($response->data);
    }

    /**
     * Delete a contact list by ID.
     *
     * @throws ApiException
     * @throws AuthenticationException
     *
     * @see https://dev.emarsys.com/docs/core-api-reference/r3jmj5jqerb9n-delete-a-contact-list
     */
    public function delete(int $contactListId): bool
    {
        $this->httpClient->delete(sprintf('%s/%d/deletelist', self::ENDPOINT, $contactListId));

        return true;
    }
}
