<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Exceptions;

/**
 * Exception thrown when an API request fails.
 */
class ApiException extends EmarsysException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        private readonly ?int $httpStatusCode = null,
        private readonly ?array $responseBody = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}
