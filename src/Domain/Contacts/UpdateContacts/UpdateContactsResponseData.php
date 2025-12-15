<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\UpdateContacts;

use Hobbii\Emarsys\Domain\ValueObjects\Response;
use InvalidArgumentException;

final readonly class UpdateContactsResponseData
{
    public function __construct(
        public array $ids,
        public ?array $errors,
    ) {}

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public static function fromResponse(Response $response): self
    {
        $ids = $response->dataAsArray()['ids'] ?? throw new InvalidArgumentException('Missing "ids" in data response');
        $errors = $response->dataAsArray()['errors'] ?? null;

        return new self(
            ids: $ids['ids'] ?? [],
            errors: $errors
        );
    }
}
