<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ContactLists\DTOs;

/**
 * Request DTO for creating a contact list.
 *
 * @see https://dev.emarsys.com/docs/core-api-reference/enmevkj1fi016-create-a-contact-list
 */
class CreateContactList
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public string $keyId = 'email',
        /** @var array<int|string|array<int>> */
        public array $externalIds = [],
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
            'key_id' => $this->keyId,
            'external_ids' => $this->externalIds,
        ], fn ($value) => $value !== null);
    }
}
