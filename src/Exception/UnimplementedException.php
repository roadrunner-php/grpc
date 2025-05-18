<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Exception;

use Spiral\RoadRunner\GRPC\StatusCode;

/**
 * @final
 */
class UnimplementedException extends GRPCException
{
    protected const CODE = StatusCode::UNIMPLEMENTED;
}
