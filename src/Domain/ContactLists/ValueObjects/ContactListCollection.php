<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ContactLists\ValueObjects;

use InvalidArgumentException;

/**
 * ContactList collection
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
        foreach ($items as $item) {
            if (! $item instanceof ContactList) {
                throw new InvalidArgumentException('All items must be instances of ContactList');
            }
        }
    }

    /**
     * Create a collection from data.
     *
     * @param  array<int,array<string,mixed>|ContactList>  $data
     */
    public static function from(array $data): self
    {
        $contactLists = array_map(fn ($item) => $item instanceof ContactList ? $item : ContactList::from($item), $data);

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
