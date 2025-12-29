<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contracts;

use JsonSerializable;

/**
 * Implement this interface to create request objects that can be sent to the Emarsys API.
 */
interface RequestInterface extends JsonSerializable
{
    /**
     * Get the HTTP method for the request.
     */
    public function method(): string;

    /**
     * Get the API endpoint for the request.
     *
     * Guzzle follows RFC 3986 URI resolution rules - if the base_uri does not end with /, it’s considered a file.
     */
    public function endpoint(): string;

    /**
     * Get the query parameters for the request.
     *
     * @return array<string,mixed>
     */
    public function query(): array;
}
