<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\DTO;

use RuntimeException;

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
        public ?int $count = null
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
            id: (int) $data['id'] ?? throw new RuntimeException('Missing id field'),
            name: $data['name'] ?? throw new RuntimeException('Missing name field'),
            description: $data['description'] ?? null,
            created: $data['created'] ?? null,
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
            'count' => $this->count,
        ], fn ($value) => $value !== null);
    }
}
