<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Integration;

use Hobbii\Emarsys\Client;

abstract class AbstractIntegrationTest
{
    public function __construct(protected readonly Client $client) {}

    abstract public function run(array $args): void;
}
