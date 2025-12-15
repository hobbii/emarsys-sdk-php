<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Enums;

enum OptInStatus: int
{
    case TRUE = 1;
    case FALSE = 2;

    public function label(): string
    {
        return $this->value === self::TRUE->value ? 'Yes' : 'No';
    }
}
