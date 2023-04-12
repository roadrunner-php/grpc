<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC;

use Spiral\RoadRunner\GRPC\Exception\InvokeException;

/**
 * Responsible for data marshalling/unmarshalling and method invocation.
 */
interface InvokerInterface
{
    /**
     * Call a service with the given method and input and return response message converted to string.
     *
     * @throws InvokeException
     */
    public function invoke(ServiceInterface $service, Method $method, ContextInterface $ctx, ?string $input): string;
}
