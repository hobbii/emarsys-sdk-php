<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ValueObjects;

use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

readonly class Response
{
    public function __construct(
        public Reply $reply,
        public int|string|array|null $data
    ) {}

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

        $reply = new Reply(
            $data['replyCode'] ?? 0,
            $data['replyText'] ?? '',
        );

        return new self(
            reply: $reply,
            data: $data['data'] ?? null,
        );
    }
}
