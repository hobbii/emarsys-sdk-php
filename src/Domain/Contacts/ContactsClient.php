<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts;

use Hobbii\Emarsys\Domain\BaseClient;
use Hobbii\Emarsys\Domain\Contacts\GetContactData\GetContactDataRequest;
use Hobbii\Emarsys\Domain\Contacts\GetContactData\GetContactDataResponse;
use Hobbii\Emarsys\Domain\Contacts\UpdateContacts\UpdateContactsRequest;
use Hobbii\Emarsys\Domain\Contacts\UpdateContacts\UpdateContactsResponseData;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;

/**
 * Service for managing Emarsys Contacts.
 */
class ContactsClient
{
    private const ENDPOINT = 'contact';

    public function __construct(
        private readonly BaseClient $client
    ) {}

    /**
     * Get contact data by identifiers.
     *
     * Returns the field values of the contacts specified by either their internal
     * identifiers or by a custom property. It is recommended to use the `id` or `uid`
     * fields to identify contacts to avoid conflicts when using non-unique fields.
     *
     * Note: The maximum number of objects per request is 1000.
     *
     * @throws ApiException
     * @throws AuthenticationException
     *
     * @see https://dev.emarsys.com/docs/core-api-reference/blzojxt3ga5be-get-contact-data
     */
    public function getContactData(GetContactDataRequest $request): GetContactDataResponse
    {
        $response = $this->client->post(self::ENDPOINT.'/getdata', $request->toArray());

        return GetContactDataResponse::fromResponse($response);
    }

    /**
     * Update contacts in Emarsys.
     *
     * Updates existing contacts or creates new ones if they do not exist.
     * The contacts are identified by the specified key field (keyId).
     *
     * Note: The maximum number of contacts per request is 1000.
     *
     * @throws ApiException
     * @throws AuthenticationException
     *
     * @see https://dev.emarsys.com/docs/core-api-reference/f8ljhut3ac2i1-update-contacts
     */
    public function updateContact(UpdateContactsRequest $request): UpdateContactsResponseData
    {
        $endpoint = self::ENDPOINT;

        if ($request->createIfNotExists) {
            $endpoint .= '?createIfNotExists=1';
        }

        $response = $this->client->put($endpoint, $request->toRequestData());

        return UpdateContactsResponseData::fromResponse($response);
    }
}
