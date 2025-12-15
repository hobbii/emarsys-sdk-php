<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\ValueObjects;

use ArrayAccess;
use ArrayIterator;
use Hobbii\Emarsys\Domain\Enums\ContactSystemFieldId;
use Hobbii\Emarsys\Domain\Enums\OptInStatus;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, string|null|array<string|null>>
 * @implements ArrayAccess<int, string|null|array<string|null>>
 */
final readonly class ContactData implements ArrayAccess, IteratorAggregate
{
    /**
     * @param  array<int,string|null|array<string|null>>  $data
     */
    public function __construct(
        public array $data,
    ) {}

    public function getOptInStatus(): ?OptInStatus
    {
        $optInValue = $this->data[ContactSystemFieldId::OPT_IN->value] ?? null;

        return $optInValue !== null ? OptInStatus::from((int) $optInValue) : null;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // ContactData is immutable - modifications are not allowed
        throw new \BadMethodCallException('ContactData is immutable and cannot be modified.');
    }

    public function offsetUnset(mixed $offset): void
    {
        // ContactData is immutable - modifications are not allowed
        throw new \BadMethodCallException('ContactData is immutable and cannot be modified.');
    }
}
