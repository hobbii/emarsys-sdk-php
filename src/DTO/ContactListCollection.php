<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\DTO;

/**
 * Response DTO containing a list of contact lists.
 */
readonly class ContactListCollection
{
    /**
     * @param  ContactList[]  $contactLists
     */
    public function __construct(
        public array $contactLists,
        public ?array $meta = null
    ) {}

    /**
     * Create a ContactListCollection from API response data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $contactLists = [];

        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $item) {
                $contactLists[] = ContactList::fromArray($item);
            }
        }

        return new self(
            contactLists: $contactLists,
            meta: $data['meta'] ?? null
        );
    }

    /**
     * Get the contact lists as an array.
     *
     * @return ContactList[]
     */
    public function getContactLists(): array
    {
        return $this->contactLists;
    }

    /**
     * Get the count of contact lists.
     */
    public function count(): int
    {
        return count($this->contactLists);
    }

    /**
     * Check if the collection is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->contactLists);
    }
}
