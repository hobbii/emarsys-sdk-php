<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ValueObjects;

use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

readonly class Response
{
    public function __construct(
        public int $replyCode,
        public string $replyText,
        public mixed $data,
        /** @var ErrorObject[] Errors returned by the Emarsys API. */
        public array $errors,
    ) {}

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public static function fromArray(array $arr): self
    {
        $errors = [];
        if (isset($arr['errors']) && is_array($arr['errors'])) {
            foreach ($arr['errors'] as $errorData) {
                $errors[] = ErrorObject::fromArray($errorData);
            }
        }

        return new self(
            replyCode: $arr['replyCode'] ?? 0,
            replyText: $arr['replyText'] ?? '',
            data: $arr['data'] ?? null,
            errors: $errors
        );
    }

    /**
     * @throws ApiException
     */
    public static function fromPsrResponse(ResponseInterface $response): self
    {
        $body = $response->getBody()->getContents();

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($data)) {
                throw new JsonException('Expected an array. Got: ' . gettype($data));
            }
        } catch (JsonException $e) {
            throw new ApiException(
                'Invalid JSON response',
                httpStatusCode: $response->getStatusCode(),
                responseBody: $body,
                previous: $e
            );
        }

        return self::fromArray($data);
    }
}
