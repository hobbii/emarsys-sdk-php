<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ContactLists\DTOs;

use InvalidArgumentException;

/**
 * Represents a Contact List in the Emarsys system.
 *
 * @see https://dev.emarsys.com/docs/core-api-reference/axpotjvepqdla-list-contact-lists#response-body
 */
class ContactList
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description = null,
        public ?string $created = null,
    ) {}

    /**
     * Create a ContactList instance from data.
     *
     * @param  array<string,mixed>  $data
     *
     * @throws InvalidArgumentException
     */
    public static function from(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? throw new InvalidArgumentException('Missing "id" field')),
            name: $data['name'] ?? throw new InvalidArgumentException('Missing "name" field'),
            description: $data['description'] ?? null,
            created: $data['created'] ?? null,
        );
    }

    /**
     * Convert the ContactList to an array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created' => $this->created,
        ], fn ($value) => $value !== null);
    }
}
