<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\UpdateContacts;

use BackedEnum;
use Hobbii\Emarsys\Domain\Contacts\Utils;
use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Request object for updating contacts in Emarsys API.
 *
 * @see https://dev.emarsys.com/docs/core-api-reference/f8ljhut3ac2i1-update-contacts
 */
final readonly class UpdateContactsRequest implements JsonSerializable
{
    private const MAX_CONTACTS_PER_REQUEST = 1000;

    private function __construct(
        public string|int $keyId,
        /** @var array<ContactData> */
        public array $contacts,
        public bool $createIfNotExists = false,
    ) {}

    /**
     * @param  array<ContactData>  $contacts  List of contacts to be updated.
     *
     * @throws InvalidArgumentException
     */
    public static function make(
        string|int|BackedEnum $keyId,
        array $contacts,
        bool $createIfNotExists = false,
    ): self {
        self::validateContacts($contacts);

        return new self(
            keyId: Utils::normalizeKeyId($keyId),
            contacts: $contacts,
            createIfNotExists: $createIfNotExists,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'keyId' => $this->keyId,
            'contacts' => $this->contacts,
        ];
    }

    private static function validateContacts(array $contacts): void
    {
        if (empty($contacts)) {
            throw new InvalidArgumentException('Contacts array cannot be empty');
        }

        if (count($contacts) > self::MAX_CONTACTS_PER_REQUEST) {
            throw new InvalidArgumentException(sprintf('The maximum batch size is %d contacts per call. You provided %d items.', self::MAX_CONTACTS_PER_REQUEST, count($contacts)));
        }
    }
}
