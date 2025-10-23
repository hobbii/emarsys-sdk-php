<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ContactLists\DTOs;

class CreateContactListResponse
{
    /**
     * @param  array<int,string>|null  $errors  The details of any contacts not added to the list, expressed as an array that contains the error code and reason
     */
    public function __construct(
        public int $id,
        public ?array $errors,
    ) {}

    /**
     * Create a CreateContactListResponse instance from data.
     *
     * @param  array<string,mixed>  $data
     */
    public static function from(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            errors: $data['errors'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'errors' => $this->errors,
        ], fn ($value) => $value !== null);
    }
}
