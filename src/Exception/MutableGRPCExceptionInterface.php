<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Exception;

use Google\Protobuf\Internal\Message;

interface MutableGRPCExceptionInterface extends GRPCExceptionInterface
{
    /**
     * Rewrites details message in the GRPC Exception.
     *
     * @param Message[] $details
     */
    public function setDetails(array $details): void;

    /**
     * Appends details message to the GRPC Exception.
     */
    public function addDetails(Message $message): void;
}
