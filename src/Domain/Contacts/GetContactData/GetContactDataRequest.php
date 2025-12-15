<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\GetContactData;

use BackedEnum;

/**
 * Request object for getting contact data from Emarsys API.
 *
 * @see https://dev.emarsys.com/docs/core-api-reference/blzojxt3ga5be-get-contact-data
 */
final readonly class GetContactDataRequest
{
    /**
     * @param  array<int>  $fields  The field IDs to retrieve for the contacts
     * @param  string|int  $keyId  Identifies the contact by their id, uid, or custom field name/ID (e.g., 'email', '3')
     * @param  array<string>  $keyValues  Array of contact identifiers (emails, IDs, etc.)
     */
    private function __construct(
        public array $fields,
        public string|int $keyId,
        public array $keyValues,
    ) {}

    /**
     * Convert the object to an array for API request.
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

    /**
     * @param  array<int|string|BackedEnum>  $fields
     */
    public static function make(
        array $fields,
        string|int|BackedEnum $keyId,
        array $keyValues,
    ): self {
        /** @var array<int> $requestFields */
        $requestFields = [];

        foreach ($fields as $index => $field) {
            if ($field instanceof BackedEnum) {
                if (! is_int($field->value)) {
                    throw new \InvalidArgumentException('Field enum must have an integer backing value. Got '.gettype($field->value).'.');
                }
                $requestFields[$index] = $field->value;
            } else {
                $requestFields[$index] = (int) $field;
            }
        }

        if ($keyId instanceof BackedEnum) {
            $keyId = $keyId->value;
        }

        return new self(
            fields: $requestFields,
            keyId: $keyId,
            keyValues: $keyValues,
        );
    }
}
