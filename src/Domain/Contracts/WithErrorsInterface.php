<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Contracts;

interface WithErrorsInterface
{
    /**
     * Indicates whether there are any errors.
     */
    public function hasErrors(): bool;
}
