<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\DTOs;

/**
 * DTO for getting contact data from Emarsys API.
 *
 * @see https://dev.emarsys.com/docs/core-api-reference/blzojxt3ga5be-get-contact-data
 */
class GetContactData
{
    /**
     * @param  array<string>  $fields  The field names/IDs to retrieve for the contacts
     * @param  string|int  $keyId  Identifies the contact by their id, uid, or custom field name/ID (e.g., 'email', '3')
     * @param  array<string>  $keyValues  Array of contact identifiers (emails, IDs, etc.)
     */
    public function __construct(
        public array $fields,
        public string|int $keyId,
        public array $keyValues,
    ) {}

    /**
     * Convert the DTO to an array for API request.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'fields' => $this->fields,
            'keyId' => $this->keyId,
            'keyValues' => $this->keyValues,
        ];
    }
}
