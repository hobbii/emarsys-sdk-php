<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ValueObjects;

use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Base immutable data collection for value objects.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @extends Collection<TKey,TValue>
 *
 * @phpstan-consistent-constructor
 */
abstract class DataCollection extends Collection
{
    /**
     * @param  array<TKey,TValue>  $items
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $items = [])
    {
        if (static::getItemClass() === '') {
            throw new InvalidArgumentException('Item class must be defined in subclass');
        }

        $this->validateItems($items);
        parent::__construct($items);
    }

    abstract protected static function getItemClass(): string;

    /**
     * Validate all items in the collection by class type.
     *
     * @param  array<TKey,TValue>  $items
     *
     * @throws InvalidArgumentException
     */
    protected function validateItems(array $items): void
    {
        $itemClass = static::getItemClass();
        foreach ($items as $item) {
            if (! $item instanceof $itemClass) {
                throw new InvalidArgumentException('All items must be instances of '.$itemClass);
            }
        }
    }

    /**
     * Create a collection from array data.
     *
     * @param  array<int,TValue|array<string,mixed>>  $data
     */
    public static function from(array $data): static
    {
        $itemClass = static::getItemClass();
        $items = array_map(
            fn ($item) => is_object($item) ? $item : $itemClass::from($item),
            $data
        );

        return new static($items);
    }
}
