<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\ValueObjects;

use Illuminate\Support\Collection;

final readonly class ContactData
{
    /**
     * @param  array<int,string|null|array<string|null>>  $data
     */
    public function __construct(
        public array $data,
    ) {}

    /**
     * Get the data as a collection.
     *
     * @return Collection<int,string|null|array<string|null>>
     */
    public function collection(): Collection
    {
        return collect($this->data);
    }
}
