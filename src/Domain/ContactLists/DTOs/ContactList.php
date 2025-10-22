<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ContactLists\DTOs;

use RuntimeException;

/**
 * Represents a Contact List in the Emarsys system.
 *
 * @see https://dev.emarsys.com/docs/core-api-reference/axpotjvepqdla-list-contact-lists#response-body
 */
readonly class ContactList
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description = null,
        public ?string $created = null,
    ) {}

    /**
     * Create a ContactList instance from API response data.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws RuntimeException
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'] ?? throw new RuntimeException('Missing "id" field'),
            name: $data['name'] ?? throw new RuntimeException('Missing "name" field'),
            description: $data['description'] ?? null,
            created: $data['created'] ?? null,
        );
    }

    /**
     * Convert the ContactList to an array.
     *
     * @return array<string, mixed>
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
