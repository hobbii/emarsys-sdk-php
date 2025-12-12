<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contact\ValueObjects;

use InvalidArgumentException;

/**
 * Represents contact data returned from Emarsys API.
 */
readonly class ContactData
{
    /**
     * @param  array<int,string|null|array<string|null>>  $data
     */
    public function __construct(
        public int $id,
        public string $uid,
        public array $data,
    ) {}

    /**
     * Create a ContactData instance from array data.
     *
     * @throws InvalidArgumentException
     */
    public static function from(array $arr): self
    {
        if (! isset($arr['id'])) {
            throw new InvalidArgumentException('Missing id in contact data');
        }
        if (! isset($arr['uid'])) {
            throw new InvalidArgumentException('Missing uid in contact data');
        }

        $data = $arr;
        unset($data['id'], $data['uid']);

        return new self(
            id: (int) $arr['id'],
            uid: $arr['uid'],
            data: $data,
        );
    }
}
