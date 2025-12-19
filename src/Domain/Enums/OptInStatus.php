<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain\Enums;

/**
 * Emarsys Opt-In Status.
 * Values are used in contact opt-in fields in Emarsys as option ids.
 *
 * Usage:
 *     $status = OptInStatus::True; // OptInStatus enum instance
 *     $optionId = OptInStatus::True->value; // 1
 *     $bool = OptInStatus::True->toBool(); // true
 */
enum OptInStatus: int
{
    case True = 1;
    case False = 2;

    public function toBool(): bool
    {
        return $this === self::True;
    }
}
