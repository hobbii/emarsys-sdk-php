<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\DTO;

/**
 * Request DTO for creating a contact list.
 */
readonly class CreateContactListRequest
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?string $type = null
    ) {}

    /**
     * Convert the request to an array for API submission.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
        ], fn ($value) => $value !== null);
    }
}
