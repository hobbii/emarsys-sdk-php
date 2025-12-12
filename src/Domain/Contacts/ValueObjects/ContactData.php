<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\ValueObjects;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, string|null|array<string|null>>
 */
final readonly class ContactData implements IteratorAggregate
{
    /**
     * @param  array<int,string|null|array<string|null>>  $data
     */
    public function __construct(
        public array $data,
    ) {}

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }
}
