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
        public int|string|array|null $data,
        /** @var ErrorObject[] Errors returned by the Emarsys API. */
        public array $errors,
    ) {}

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    /**
     * @throws ApiException
     */
    public function dataAsInt(): int
    {
        if (is_int($this->data)) {
            return $this->data;
        }

        throw new ApiException('Response data is not an integer');
    }

    /**
     * @throws ApiException
     */
    public function dataAsString(): string
    {
        if (is_string($this->data)) {
            return $this->data;
        }

        throw new ApiException('Response data is not a string');
    }

    /**
     * @throws ApiException
     */
    public function dataAsArray(): array
    {
        if (is_array($this->data)) {
            return $this->data;
        }

        throw new ApiException('Response data is not an array');
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
                throw new JsonException('Expected an array. Got: '.gettype($data));
            }
        } catch (JsonException $e) {
            throw new ApiException('Invalid JSON response', previous: $e);
        }

        return self::fromArray($data);
    }
}
