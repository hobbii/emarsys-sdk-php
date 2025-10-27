<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain\ValueObjects;

use Hobbii\Emarsys\Domain\ValueObjects\DataCollection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DummyItem {}

/**
 * @extends DataCollection<int,DummyItem>
 */
class DummyCollection extends DataCollection
{
    protected static function getItemClass(): string
    {
        return DummyItem::class;
    }
}

final class DataCollectionTest extends TestCase
{
    public function test_can_be_created_empty(): void
    {
        $collection = new DummyCollection([]);

        $this->assertSame(0, $collection->count());
        $this->assertTrue($collection->isEmpty());
    }

    public function test_accepts_only_correct_item_type(): void
    {
        $item1 = new DummyItem;
        $item2 = new DummyItem;
        $collection = new DummyCollection([$item1, $item2]);

        $this->assertCount(2, $collection);
        $this->assertInstanceOf(DummyCollection::class, $collection);
    }

    public function test_throws_on_invalid_item_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All items must be instances of '.DummyItem::class);

        // @phpstan-ignore-next-line
        new DummyCollection([new DummyItem, new \stdClass]);
    }

    public function test_from_method_creates_collection(): void
    {
        $item = new DummyItem;
        $collection = DummyCollection::from([$item]);

        $this->assertInstanceOf(DummyCollection::class, $collection);
        $this->assertCount(1, $collection);
    }

    public function test_from_method_can_create_empty_collection(): void
    {
        $collection = DummyCollection::from([]);

        $this->assertTrue($collection->isEmpty());
    }
}
