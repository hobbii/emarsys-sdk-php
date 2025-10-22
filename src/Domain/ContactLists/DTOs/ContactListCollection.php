<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ContactLists\DTOs;

use InvalidArgumentException;

/**
 * Response DTO containing a list of contact lists.
 */
readonly class ContactListCollection
{
    /**
     * @param  ContactList[]  $items
     *
     * @throws InvalidArgumentException
     */
    public function __construct(public array $items = [])
    {
        $this->validateItems($items);
    }

    private function validateItems(array $items): void
    {
        array_walk($items, function ($item) {
            if (! $item instanceof ContactList) {
                throw new InvalidArgumentException('All items must be instances of ContactList');
            }
        });
    }

    /**
     * Create a ContactListCollection from API response data.
     *
     * @param  array<int,array<string, mixed>>  $data
     */
    public static function fromArray(array $data): self
    {
        $contactLists = [];

        foreach ($data as $item) {
            $contactLists[] = ContactList::fromArray($item);
        }

        return new self($contactLists);
    }

    /**
     * Get the count of contact lists.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Check if the collection is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
