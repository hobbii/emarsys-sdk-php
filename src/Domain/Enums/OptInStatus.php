<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Enums;

enum OptInStatus: int
{
    case True = 1;
    case False = 2;

    public function toBool(): bool
    {
        return $this === self::True;
    }
}
