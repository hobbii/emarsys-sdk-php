<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\ValueObjects;

use ArrayIterator;
use BackedEnum;
use Hobbii\Emarsys\Domain\Enums\ContactSpecialKeyId;
use Hobbii\Emarsys\Domain\Enums\ContactSystemField;
use Hobbii\Emarsys\Domain\Enums\OptInStatus;
use InvalidArgumentException;
use IteratorAggregate;
use JsonSerializable;
use Traversable;
use ValueError;

/**
 * Contact data value object that provides flexible access to contact field data.
 *
 * Supports both generic field access and type-safe getters for common fields.
 * Can be used with ContactSystemField enums or raw field IDs.
 *
 * @implements IteratorAggregate<int|string, string|null|array<string|null>>
 */
final readonly class ContactData implements IteratorAggregate, JsonSerializable
{
    /**
     * @param  array<int|string,string|null|array<string|null>>  $data  Raw contact field data
     */
    public function __construct(
        public array $data,
    ) {}

    /**
     * Check if a field exists in the contact data.
     */
    public function has(int|string|BackedEnum $key): bool
    {
        $keyId = KeyId::make($key);

        return array_key_exists($keyId->value, $this->data);
    }

    /**
     * Get contact field value by key.
     */
    public function get(int|string|BackedEnum $key): string|array|null
    {
        $keyId = KeyId::make($key);

        return $this->data[$keyId->value] ?? null;
    }

    /**
     * Get the contact's opt-in status as an enum.
     *
     * @throws InvalidArgumentException If opt-in value is not numeric or invalid
     */
    public function getOptInStatus(): ?OptInStatus
    {
        $optInValue = $this->get(ContactSystemField::optin);

        if ($optInValue === null) {
            return null;
        }

        if (! is_scalar($optInValue) || ! is_numeric($optInValue)) {
            throw new InvalidArgumentException(
                sprintf('Opt-in status must be numeric, got %s', gettype($optInValue))
            );
        }

        try {
            return OptInStatus::from((int) $optInValue);
        } catch (ValueError $e) {
            throw new InvalidArgumentException(
                'Invalid opt-in status value: '.$optInValue,
                previous: $e
            );
        }
    }

    public function getId(): ?int
    {
        $value = $this->get(ContactSpecialKeyId::id->name);

        if ($value === null || ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    public function getUid(): ?string
    {
        $value = $this->get(ContactSpecialKeyId::uid->name);

        return is_string($value) ? $value : null;
    }

    public function getEmail(): ?string
    {
        $value = $this->get(ContactSystemField::email);

        return is_string($value) ? $value : null;
    }

    public function getFirstName(): ?string
    {
        $value = $this->get(ContactSystemField::first_name);

        return is_string($value) ? $value : null;
    }

    public function getLastName(): ?string
    {
        $value = $this->get(ContactSystemField::last_name);

        return is_string($value) ? $value : null;
    }

    /**
     * @return array<int|string,string|null|array<string|null>>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Create ContactData from API response result item.
     * According to the API docs, 'id' and 'uid' are always present in the response.
     *
     * @throws InvalidArgumentException If required fields are missing or invalid
     *
     * @see https://dev.emarsys.com/docs/core-api-reference/blzojxt3ga5be-get-contact-data#response-body Get Contact Data - Response Body
     */
    public static function fromResponseResultItem(array $item): self
    {
        $contactData = new self(data: $item);

        if ($contactData->getId() === null) {
            throw new InvalidArgumentException('Contact data must have a valid numeric id');
        }

        if ($contactData->getUid() === null) {
            throw new InvalidArgumentException('Contact data must have a valid string uid');
        }

        return $contactData;
    }
}
