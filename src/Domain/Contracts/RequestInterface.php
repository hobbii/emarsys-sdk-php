<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contracts;

use JsonSerializable;

interface RequestInterface extends JsonSerializable
{
    /**
     * Get the HTTP method for the request.
     */
    public function method(): string;

    /**
     * Get the API endpoint for the request.
     */
    public function endpoint(): string;

    /**
     * Get the query parameters for the request.
     *
     * @return array<string,mixed>
     */
    public function query(): array;

    /**
     * Get the response class associated with the request.
     * The class must implement ResponseDataInterface.
     */
    public function responseDataClass(): string;
}
