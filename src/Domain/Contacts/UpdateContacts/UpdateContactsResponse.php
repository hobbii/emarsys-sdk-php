<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contacts\UpdateContacts;

use Hobbii\Emarsys\Domain\Contracts\ResponseInterface;
use Hobbii\Emarsys\Domain\Contracts\WithErrorsInterface;
use Hobbii\Emarsys\Domain\Traits\WithErrors;
use Hobbii\Emarsys\Domain\Traits\WithReply;
use Hobbii\Emarsys\Domain\ValueObjects\ErrorObject;
use Hobbii\Emarsys\Domain\ValueObjects\Reply;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use InvalidArgumentException;

final readonly class UpdateContactsResponse implements ResponseInterface, WithErrorsInterface
{
    use WithErrors;
    use WithReply;

    /**
     * @param  array<int,string>  $ids  The list of IDs of the contacts that were updated
     */
    private function __construct(
        public Reply $reply,
        public array $ids,
        public ?array $errors
    ) {}

    public static function fromResponse(Response $response): self
    {
        $responseData = $response->dataAsArray();
        $ids = $responseData['ids'] ?? null;

        if (! isset($ids)) {
            throw new InvalidArgumentException('Missing "ids" in data response');
        }

        $errors = $responseData['errors'] ?? null;

        if (is_array($errors)) {
            $errors = array_map(ErrorObject::fromArray(...), $errors);
        }

        return new self(
            reply: $response->reply,
            ids: $ids,
            errors: $errors
        );
    }
}
