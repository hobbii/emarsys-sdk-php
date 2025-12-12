<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contact;

use Hobbii\Emarsys\Domain\BaseClient;
use Hobbii\Emarsys\Domain\Contact\DTOs\GetContactData;
use Hobbii\Emarsys\Domain\Contact\ValueObjects\GetContactDataResponse;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;

/**
 * Service for managing Emarsys Contacts.
 */
class ContactClient
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
    public function getData(GetContactData $input): GetContactDataResponse
    {
        $response = $this->client->post(self::ENDPOINT.'/getdata', $input->toArray());

        if ($response->data === null) {
            throw new ApiException('Invalid response format: missing data field');
        }

        return GetContactDataResponse::fromResponse($response);
    }
}
