<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\GetContactData;

use BackedEnum;
use Hobbii\Emarsys\Domain\Contacts\Utils;
use Hobbii\Emarsys\Domain\Contracts\RequestInterface;

/**
 * Request object for getting contact data from Emarsys API.
 *
 * @see https://dev.emarsys.com/docs/core-api-reference/blzojxt3ga5be-get-contact-data
 */
final readonly class GetContactDataRequest implements RequestInterface
{
    /**
     * @param  array<int>  $fields  The field IDs to retrieve for the contacts
     */
    private function __construct(
        public array $fields,
        public string|int $keyId,
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
     * @param  string|int|BackedEnum  $keyId  Identifies the contact by their id, uid, or custom field name/ID (e.g., 'email', '3')
     * @param  array<string>  $keyValues  Array of contact identifiers (emails, ids, uid)
     *
     * @throws \InvalidArgumentException if any field ID enum does not have an integer backing value
     */
    public static function make(
        array $fields,
        string|int|BackedEnum $keyId,
        array $keyValues,
    ): self {
        /** @var array<int> $requestFields */
        $requestFields = array_map(Utils::normalizeFieldId(...), $fields);

        return new self(
            fields: $requestFields,
            keyId: Utils::normalizeKeyId($keyId),
            keyValues: $keyValues,
        );
    }
}
