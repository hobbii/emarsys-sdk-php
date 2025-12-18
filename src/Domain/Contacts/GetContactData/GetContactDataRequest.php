<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\GetContactData;

use BackedEnum;
use Hobbii\Emarsys\Domain\Contacts\ValueObjects\KeyId;
use Hobbii\Emarsys\Domain\Contracts\RequestInterface;

/**
 * Request object for getting contact data from Emarsys API.
 *
 * @see https://dev.emarsys.com/docs/core-api-reference/blzojxt3ga5be-get-contact-data
 */
final readonly class GetContactDataRequest implements RequestInterface
{
    /**
     * @param  KeyId[]  $fields  The field IDs to retrieve for the contacts
     */
    private function __construct(
        public array $fields,
        public KeyId $keyId,
        public array $keyValues,
    ) {}

    public function method(): string
    {
        return 'POST';
    }

    public function endpoint(): string
    {
        return 'contact/getdata';
    }

    public function query(): array
    {
        return [];
    }

    public function responseDataClass(): string
    {
        return GetContactDataResponseData::class;
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'fields' => $this->fields,
            'keyId' => $this->keyId,
            'keyValues' => $this->keyValues,
        ];
    }

    /**
     * @param  array<int|string|BackedEnum>  $fields  The field IDs to retrieve for the contacts
     * @param  int|string|BackedEnum  $keyId  Identifies the contact by their id, uid, or custom field name/ID (e.g., 'email', '3')
     * @param  array<string>  $keyValues  Array of contact identifiers (emails, ids, uid)
     *
     * @throws \InvalidArgumentException if any field ID enum does not have an integer backing value
     */
    public static function make(
        array $fields,
        int|string|BackedEnum $keyId,
        array $keyValues,
    ): self {
        $requestFields = array_map(KeyId::make(...), $fields);

        return new self(
            fields: $requestFields,
            keyId: KeyId::make($keyId),
            keyValues: $keyValues,
        );
    }
}
