<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ContactLists\ValueObjects;

use Hobbii\Emarsys\Domain\ValueObjects\DataCollection;

/**
 * ContactList collection
 *
 * @extends DataCollection<int,ContactList>
 */
final class ContactListCollection extends DataCollection
{
    protected static function getItemClass(): string
    {
        return ContactList::class;
    }
}
