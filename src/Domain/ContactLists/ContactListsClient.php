<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ContactLists;

use Hobbii\Emarsys\Domain\Client;
use Hobbii\Emarsys\Domain\ContactLists\DTOs\CreateContactList;
use Hobbii\Emarsys\Domain\ContactLists\ValueObjects\ContactListCollection;
use Hobbii\Emarsys\Domain\ContactLists\ValueObjects\CreateContactListResponse;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;

/**
 * Service for managing Emarsys Contact Lists.
 */
class ContactListsClient
{
    private const ENDPOINT = 'contactlist';

    public function __construct(
        private readonly Client $client
    ) {}

    /**
     * Create a new contact list.
     *
     * @return int The ID of the created contact list.
     *
     * @throws ApiException
     * @throws AuthenticationException
     *
     * @see https://dev.emarsys.com/docs/core-api-reference/enmevkj1fi016-create-a-contact-list
     */
    public function create(CreateContactList $data): int
    {
        $response = $this->client->post(self::ENDPOINT, $data->toArray());

        if ($response->data === null) {
            throw new ApiException('Invalid response format: missing data field');
        }

        $responseObject = CreateContactListResponse::from($response->dataAsArray());

        return $responseObject->id;
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
        $response = $this->client->get(self::ENDPOINT);

        return ContactListCollection::from($response->dataAsArray());
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
        $this->client->delete(sprintf('%s/%d/deletelist', self::ENDPOINT, $contactListId));

        return true;
    }
}
