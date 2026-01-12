<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Traits;

use Hobbii\Emarsys\Domain\ValueObjects\ErrorObject;

trait WithErrors
{
    /**
     * @var ErrorObject[] Array of error objects
     **/
    public readonly array $errors;

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
