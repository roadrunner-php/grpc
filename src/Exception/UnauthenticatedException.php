<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Exception;

use Spiral\RoadRunner\GRPC\StatusCode;

class UnauthenticatedException extends InvokeException
{
    protected const CODE = StatusCode::UNAUTHENTICATED;
}
