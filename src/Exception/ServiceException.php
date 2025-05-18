<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Exception;

use Spiral\RoadRunner\GRPC\StatusCode;

/**
 * @final
 */
class ServiceException extends GRPCException
{
    /**
     * {@inheritDoc}
     */
    protected const CODE = StatusCode::INTERNAL;
}
