<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\GetContactData;

use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
use Hobbii\Emarsys\Domain\Contracts\ResponseDataInterface;
use Hobbii\Emarsys\Domain\Contracts\WithErrorsInterface;
use Hobbii\Emarsys\Domain\Traits\WithErrors;
use Hobbii\Emarsys\Domain\Traits\WithReply;
use Hobbii\Emarsys\Domain\ValueObjects\ErrorObject;
use Hobbii\Emarsys\Domain\ValueObjects\Reply;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use InvalidArgumentException;

/**
 * Response object for getting contact data from Emarsys API.
 *
 * @see https://dev.emarsys.com/docs/core-api-reference/blzojxt3ga5be-get-contact-data
 */
final readonly class GetContactDataResponseData implements ResponseDataInterface, WithErrorsInterface
{
    use WithErrors;
    use WithReply;

    /**
     * @param  ContactData[]  $contactDataResult  The array of retrieved contact data objects
     */
    private function __construct(
        public array $contactDataResult,
        public array $errors,
        protected Reply $reply,
    ) {}

    public function hasResult(): bool
    {
        return ! empty($this->contactDataResult);
    }

    public function getFirstContactData(): ?ContactData
    {
        return $this->contactDataResult[0] ?? null;
    }

    /**
     * Create a GetContactDataResponse instance from Response.
     *
     * @throws InvalidArgumentException
     */
    public static function fromResponse(Response $response): self
    {
        $responseData = $response->dataAsArray();
        $responseDataResult = $responseData['result'] ?? null;

        if ($responseDataResult === null) {
            throw new InvalidArgumentException('Missing "result" in contact data response');
        }

        $contactDataResult = [];

        if (is_array($responseDataResult)) {
            $contactDataResult = array_map(ContactData::fromResponseResultData(...), $responseDataResult);
        }

        $responseDataErrors = $responseData['errors'] ?? null;
        $errors = [];

        if (is_array($responseDataErrors)) {
            $errors = array_map(ErrorObject::fromArray(...), $responseDataErrors);
        }

        return new self($contactDataResult, $errors, $response->reply);
    }
}
