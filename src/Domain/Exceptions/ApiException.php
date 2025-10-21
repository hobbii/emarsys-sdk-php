<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Exceptions;

use GuzzleHttp\Exception\RequestException;

/**
 * Exception thrown when an API request fails.
 */
class ApiException extends EmarsysException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        private readonly ?string $uri = null,
        private readonly ?int $httpStatusCode = null,
        private readonly string|array|null $responseBody = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    public function getResponseBody(): string|array|null
    {
        return $this->responseBody;
    }

    public function withMessage(string $message): self
    {
        $clone = clone $this;
        $clone->message = $message;
        return $clone;
    }

    public static function fromRequestException(RequestException $exception): self
    {
        $response = $exception->getResponse();
        $uri = (string) $exception->getRequest()->getUri();
        $statusCode = $response ? $response->getStatusCode() : null;
        $body = $response?->getBody()->getContents();

        return new self(
            message: $exception->getMessage(),
            uri: $uri,
            httpStatusCode: $statusCode,
            responseBody: $body,
            previous: $exception
        );
    }
}
