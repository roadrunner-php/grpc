<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Exception;

use Spiral\RoadRunner\GRPC\StatusCode;

class NotFoundException extends InvokeException
{
    protected const CODE = StatusCode::NOT_FOUND;
}
