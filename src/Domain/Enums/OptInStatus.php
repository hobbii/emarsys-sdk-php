<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Enums;

enum OptInStatus: int
{
    case TRUE = 1;
    case FALSE = 2;

    public function isTrue(): bool
    {
        return $this === self::TRUE;
    }

    public function isFalse(): bool
    {
        return $this === self::FALSE;
    }

    public function label(): string
    {
        return $this->value === self::TRUE->value ? 'Yes' : 'No';
    }
}
