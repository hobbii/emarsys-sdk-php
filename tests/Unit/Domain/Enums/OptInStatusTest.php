<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Enums;

use Hobbii\Emarsys\Domain\Enums\OptInStatus;
use PHPUnit\Framework\TestCase;

final class OptInStatusTest extends TestCase
{
    public function test_enum_values_are_correct(): void
    {
        $this->assertSame(1, OptInStatus::True->value);
        $this->assertSame(2, OptInStatus::False->value);
    }

    public function test_as_bool_returns_true_for_true_status(): void
    {
        $status = OptInStatus::True;
        $result = $status->asBool();

        $this->assertTrue($result);
    }

    public function test_as_bool_returns_false_for_false_status(): void
    {
        $status = OptInStatus::False;
        $result = $status->asBool();

        $this->assertFalse($result);
    }

    public function test_from_bool_creates_true_status_from_true(): void
    {
        $result = OptInStatus::fromBool(true);

        $this->assertSame(OptInStatus::True, $result);
    }

    public function test_from_bool_creates_false_status_from_false(): void
    {
        $result = OptInStatus::fromBool(false);

        $this->assertSame(OptInStatus::False, $result);
    }

    public function test_roundtrip_conversion_maintains_consistency(): void
    {
        $this->assertTrue(OptInStatus::fromBool(true)->asBool());
        $this->assertFalse(OptInStatus::fromBool(false)->asBool());

        $this->assertSame(OptInStatus::True, OptInStatus::fromBool(OptInStatus::True->asBool()));
        $this->assertSame(OptInStatus::False, OptInStatus::fromBool(OptInStatus::False->asBool()));
    }
}
