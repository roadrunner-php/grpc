<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests\Stub;

use Spiral\RoadRunner\GRPC\ServiceInterface;

interface NonStringNameInterface extends ServiceInterface
{
    public const NAME = 42;
}
