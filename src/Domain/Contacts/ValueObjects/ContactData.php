<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\ValueObjects;

use ArrayIterator;
use BackedEnum;
use Hobbii\Emarsys\Domain\Enums\ContactSystemField;
use Hobbii\Emarsys\Domain\Enums\OptInStatus;
use InvalidArgumentException;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @implements IteratorAggregate<int, string|null|array<string|null>>
 */
final readonly class ContactData implements IteratorAggregate, JsonSerializable
{
    /**
     * @param  array<int,string|null|array<string|null>>  $data
     */
    public function __construct(
        public array $data,
    ) {}

    public function has(int|string|BackedEnum $key): bool
    {
        $keyId = $this->normalizeKey($key);

        return array_key_exists($keyId, $this->data);
    }

    public function get(int|string|BackedEnum $key): string|array|null
    {
        $keyId = $this->normalizeKey($key);

        return $this->data[$keyId] ?? null;
    }

    public function getOptInStatus(): ?OptInStatus
    {
        $optInValue = $this->get(ContactSystemField::optin);

        if ($optInValue === null) {
            return null;
        }

        if (! is_numeric($optInValue)) {
            throw new InvalidArgumentException('Opt-in status value is not numeric');
        }

        return OptInStatus::from((int) $optInValue);
    }

    /**
     * @return array<int,string|null|array<string|null>>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    private function normalizeKey(int|string|BackedEnum $key): int|string
    {
        return $key instanceof BackedEnum ? $key->value : $key;
    }

    public static function fromResponseResultItem(array $item): self
    {
        if (! isset($item['id'])) {
            throw new InvalidArgumentException('Missing id in contact data result');
        }

        if (! isset($item['uid'])) {
            throw new InvalidArgumentException('Missing uid in contact data result');
        }

        return new self(data: $item);
    }
}
