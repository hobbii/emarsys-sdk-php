<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\GetContactData;

use Hobbii\Emarsys\Domain\Contacts\ValueObjects\ContactData;
use Hobbii\Emarsys\Domain\Contracts\ResponseInterface;
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
final readonly class GetContactDataResponse implements ResponseInterface, WithErrorsInterface
{
    use WithErrors;
    use WithReply;

    /**
     * @param  ContactData[]  $result  The array of retrieved contact data objects
     */
    private function __construct(
        public Reply $reply,
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

    /**
     * Create a GetContactDataResponse instance from Response.
     *
     * @throws InvalidArgumentException
     */
    public static function fromResponse(Response $response): self
    {
        $result = $response->dataGet('result', []);

        if (is_bool($result) && $result === false) {
            $result = [];
        }

        if (! is_array($result)) {
            throw new InvalidArgumentException('Invalid "result" in data response');
        }

        $errors = $response->dataGet('errors', []);

        if (! is_array($errors)) {
            throw new InvalidArgumentException('Invalid "errors" in data response');
        }

        $result = array_map(ContactData::fromResponseResultItem(...), $result);
        $errors = array_map(ErrorObject::fromArray(...), $errors);

        return new self(
            reply: $response->reply,
            result: $result,
            errors: $errors
        );
    }
}
