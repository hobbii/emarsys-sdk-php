<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\ValueObjects;

use Stringable;

readonly class ErrorObject implements Stringable
{
    public function __construct(
        public string $key,
        public string $errorCode,
        public string $errorMsg,
    ) {}

    public function __toString(): string
    {
        return sprintf('%s: %s (%s)', $this->key, $this->errorMsg, $this->errorCode);
    }

    /**
     * @param  array<string,string>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'] ?? '',
            errorCode: $data['errorCode'] ?? '',
            errorMsg: $data['errorMsg'] ?? ''
        );
    }
}
