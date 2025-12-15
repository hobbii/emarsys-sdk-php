<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\UpdateContacts;

use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
use InvalidArgumentException;

/**
 * Request object for updating contacts in Emarsys API.
 *
 * @see https://dev.emarsys.com/docs/core-api-reference/f8ljhut3ac2i1-update-contacts
 */
final readonly class UpdateContactsRequest
{
    private const MAX_CONTACTS_PER_REQUEST = 1000;

    /**
     * @param  array<ContactData>  $contacts  List of contacts to be updated.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string|int $keyId,
        public array $contacts,
        public bool $createIfNotExists = false,
    ) {
        if (empty($contacts)) {
            throw new InvalidArgumentException('Contacts array cannot be empty');
        }

        if (count($contacts) > self::MAX_CONTACTS_PER_REQUEST) {
            throw new InvalidArgumentException(sprintf('The maximum batch size is %d contacts per call. You provided %d items.', self::MAX_CONTACTS_PER_REQUEST, count($contacts)));
        }
    }

    public function toRequestData(): array
    {
        return [
            'keyId' => $this->keyId,
            'contacts' => array_map(
                fn (ContactData $contact) => $contact->data,
                $this->contacts
            ),
        ];
    }
}
