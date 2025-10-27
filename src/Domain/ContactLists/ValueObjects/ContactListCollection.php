<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ContactLists\ValueObjects;

use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * ContactList collection
 *
 * @extends Collection<int,ContactList>
 */
final class ContactListCollection extends Collection
{
    /**
     * @param  array<int,ContactList>  $items
     */
    public function __construct(array $items = [])
    {
        $this->validateItems($items);
        parent::__construct($items);
    }

    /**
     * @param  array<int,ContactList>  $items
     */
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
}
