<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Exception;

use Google\Protobuf\Internal\Message;
use Spiral\RoadRunner\GRPC\StatusCode;

/**
 * @psalm-import-type StatusCodeType from StatusCode
 */
interface GRPCExceptionInterface extends \Throwable
{
    /**
     * Returns GRPC exception status code.
     *
     * @return StatusCodeType
     * @psalm-mutation-free
     */
    #[\ReturnTypeWillChange]
    public function getCode();

    /**
     * Get the collection of protobuf messages for describing caused error.
     *
     * @return Message[]
     */
    public function getDetails(): array;
}
