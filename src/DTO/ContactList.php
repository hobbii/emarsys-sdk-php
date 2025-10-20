<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\DTO;

/**
 * Represents a Contact List in the Emarsys system.
 */
readonly class ContactList
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description = null,
        public ?string $created = null,
        public ?string $type = null,
        public ?int $count = null
    ) {}

    /**
     * Create a ContactList instance from API response data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            created: $data['created'] ?? null,
            type: $data['type'] ?? null,
            count: isset($data['count']) ? (int) $data['count'] : null
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
            'type' => $this->type,
            'count' => $this->count,
        ], fn ($value) => $value !== null);
    }
}
