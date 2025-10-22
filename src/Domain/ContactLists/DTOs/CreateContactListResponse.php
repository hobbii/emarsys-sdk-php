<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ContactLists\DTOs;

readonly class CreateContactListResponse
{
    public function __construct(
        public int $id,
        public ?array $errors,
    ) {}

    public static function fromArray(array $data): self
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
