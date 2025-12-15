<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\GetContactData;

use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
use Hobbii\Emarsys\Domain\ValueObjects\ErrorObject;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use InvalidArgumentException;

final readonly class GetContactDataResponse
{
    /**
     * @param  array<ContactData>|null  $result  The array of retrieved contact data objects
     * @param  array<ErrorObject>|null  $errors  The details of any contacts not retrieved, expressed as an array that contains the error code and reason
     */
    public function __construct(
        public ?array $result,
        public ?array $errors,
    ) {}

    /**
     * Create a GetContactDataResponse instance from Response.
     *
     * @throws InvalidArgumentException
     */
    public static function fromResponse(Response $response): self
    {
        $responseData = $response->dataAsArray();

        if (! isset($responseData['result'])) {
            throw new InvalidArgumentException('Missing "result" in contact data response');
        }

        $dataResult = $responseData['result'];
        $result = null;

        if (is_array($dataResult)) {
            $result = array_map(ContactData::fromResponseResultData(...), $dataResult);
        }

        $dataErrors = $responseData['errors'] ?? null;
        $errors = null;

        if (is_array($dataErrors)) {
            $errors = array_map(ErrorObject::fromArray(...), $dataErrors);
        }

        return new self($result, $errors);
    }
}
