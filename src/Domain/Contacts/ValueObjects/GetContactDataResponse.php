<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\ValueObjects;

use Hobbii\Emarsys\Domain\ValueObjects\Response;
use InvalidArgumentException;

readonly class GetContactDataResponse
{
    /**
     * @param  array<int,ContactData>  $result  The list of contacts added to the contact list
     * @param  array<int,string>|null  $errors  The details of any contacts not added to the list, expressed as an array that contains the error code and reason
     */
    public function __construct(
        public array $result,
        public ?array $errors,
    ) {}

    /**
     * Create a GetContactDataResponse instance from Response.
     *
     * @throws InvalidArgumentException
     */
    public static function fromResponse(Response $response): self
    {
        $result = $response->dataAsArray()['result'] ?? throw new InvalidArgumentException('Missing result in contact data response');
        $errors = $response->dataAsArray()['errors'] ?? null;

        // Convert array to ContactData objects
        $contactData = [];
        foreach ($result as $data) {
            $contactData[] = ContactData::from($data);
        }

        return new self(
            result: $contactData,
            errors: $errors
        );
    }
}
