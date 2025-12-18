<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\UpdateContacts;

use Hobbii\Emarsys\Domain\ValueObjects\ErrorObject;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use InvalidArgumentException;

final readonly class UpdateContactsResponseData
{
    /**
     * @param  array<int,string>  $ids  The list of IDs of the contacts that were updated
     * @param  ErrorObject[]  $errors  The details of any contacts not updated, expressed as an array that contains the error code and reason
     */
    public function __construct(
        public array $ids,
        public array $errors
    ) {}

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public static function fromResponse(Response $response): self
    {
        $responseData = $response->dataAsArray();

        if (! isset($responseData['ids'])) {
            throw new InvalidArgumentException('Missing "ids" in data response');
        }

        $errors = [];

        foreach ($responseData['errors'] ?? [] as $key => $errorData) {
            foreach ($errorData as $errorCode => $errorMessage) {
                $errors[] = new ErrorObject((string) $key, (int) $errorCode, $errorMessage);
            }
        }

        return new self($responseData['ids'], $errors);
    }
}
