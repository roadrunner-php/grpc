<?php

/**
 * This file is part of RoadRunner package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\GRPC {

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\ContextInterface instead.
     */
    interface ContextInterface extends \Spiral\RoadRunner\GRPC\ContextInterface
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\InvokerInterface instead.
     */
    interface InvokerInterface extends \Spiral\RoadRunner\GRPC\InvokerInterface
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\ServiceInterface instead.
     */
    interface ServiceInterface extends \Spiral\RoadRunner\GRPC\ServiceInterface
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\Context instead.
     */
    final class Context extends \Spiral\RoadRunner\GRPC\Context implements \Spiral\GRPC\ContextInterface
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\Invoker instead.
     */
    final class Invoker extends \Spiral\RoadRunner\GRPC\Invoker implements \Spiral\GRPC\InvokerInterface
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\Method instead.
     */
    final class Method extends \Spiral\RoadRunner\GRPC\Method
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\ResponseHeaders instead.
     */
    final class ResponseHeaders extends \Spiral\RoadRunner\GRPC\ResponseHeaders
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\Server instead.
     */
    final class Server extends \Spiral\RoadRunner\GRPC\Server
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\ServiceWrapper instead.
     */
    final class ServiceWrapper extends \Spiral\RoadRunner\GRPC\ServiceWrapper
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\StatusCode instead.
     */
    final class StatusCode extends \Spiral\RoadRunner\GRPC\StatusCode
    {
    }
}

namespace Spiral\GRPC\Exception {

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\Exception\GRPCExceptionInterface instead.
     */
    interface GRPCExceptionInterface extends \Spiral\RoadRunner\GRPC\Exception\GRPCExceptionInterface
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\Exception\MutableGRPCExceptionInterface instead.
     */
    interface MutableGRPCExceptionInterface extends \Spiral\RoadRunner\GRPC\Exception\MutableGRPCExceptionInterface,
                                                    \Spiral\GRPC\Exception\GRPCExceptionInterface
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\Exception\GRPCException instead.
     */
    class GRPCException extends \Spiral\RoadRunner\GRPC\Exception\GRPCException implements
        \Spiral\GRPC\Exception\MutableGRPCExceptionInterface
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\Exception\InvokeException instead.
     */
    class InvokeException extends \Spiral\RoadRunner\GRPC\Exception\InvokeException
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\Exception\NotFoundException instead.
     */
    class NotFoundException extends \Spiral\RoadRunner\GRPC\Exception\NotFoundException
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\Exception\ServiceException instead.
     */
    class ServiceException extends \Spiral\RoadRunner\GRPC\Exception\ServiceException
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\Exception\UnauthenticatedException instead.
     */
    class UnauthenticatedException extends \Spiral\RoadRunner\GRPC\Exception\UnauthenticatedException
    {
    }

    /**
     * @deprecated Since RoadRunner 2.0, use Spiral\RoadRunner\GRPC\Exception\UnimplementedException instead.
     */
    class UnimplementedException extends \Spiral\RoadRunner\GRPC\Exception\UnimplementedException
    {
    }
}
