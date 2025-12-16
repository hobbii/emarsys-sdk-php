<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\GetContactData;

use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
use Hobbii\Emarsys\Domain\Contracts\ResponseDataInterface;
use Hobbii\Emarsys\Domain\ValueObjects\ErrorObject;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use InvalidArgumentException;

/**
 * Response object for getting contact data from Emarsys API.
 *
 * @see https://dev.emarsys.com/docs/core-api-reference/blzojxt3ga5be-get-contact-data
 */
final readonly class GetContactDataResponseData implements ResponseDataInterface
{
    /**
     * @param  array<ContactData>  $result  The array of retrieved contact data objects
     * @param  array<ErrorObject>  $errors  The details of any contacts not retrieved, expressed as an array that contains the error code and reason
     */
    public function __construct(
        public array $result,
        public array $errors,
    ) {}

    public function hasResult(): bool
    {
        return ! empty($this->result);
    }

    public function getFirstContactData(): ?ContactData
    {
        return $this->result[0] ?? null;
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    /**
     * Create a GetContactDataResponse instance from Response.
     *
     * @throws InvalidArgumentException
     */
    public static function fromResponse(Response $response): self
    {
        $responseData = $response->dataAsArray();
        $dataResult = $responseData['result'] ?? null;

        if ($dataResult === null) {
            throw new InvalidArgumentException('Missing "result" in contact data response');
        }

        $result = [];

        if (is_array($dataResult)) {
            $result = array_map(ContactData::fromResponseResultData(...), $dataResult);
        }

        $dataErrors = $responseData['errors'] ?? null;
        $errors = [];

        if (is_array($dataErrors)) {
            $errors = array_map(ErrorObject::fromArray(...), $dataErrors);
        }

        return new self($result, $errors);
    }
}
