<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC;

use Google\Protobuf\Internal\Message;
use Spiral\RoadRunner\GRPC\Exception\InvokeException;

final class Invoker implements InvokerInterface
{
    private const ERROR_METHOD_RETURN =
        'Method %s must return an object that instance of %s, ' .
        'but the result provides type of %s';

    private const ERROR_METHOD_IN_TYPE =
        'Method %s input type must be an instance of %s, ' .
        'but the input is type of %s';

    public function invoke(
        ServiceInterface $service,
        Method $method,
        ContextInterface $ctx,
        string|Message|null $input,
    ): string {
        /** @var callable $callable */
        $callable = [$service, $method->name];

        $input = $input instanceof Message ? $input : $this->makeInput($method, $input);

        /** @var Message $message */
        $message = $callable($ctx, $input);

        \assert($this->assertResultType($method, $message));

        try {
            return $message->serializeToString();
        } catch (\Throwable $e) {
            throw InvokeException::create($e->getMessage(), StatusCode::INTERNAL, $e);
        }
    }

    /**
     * Checks that the result from the GRPC service method returns the Message object.
     *
     * @throws \BadFunctionCallException
     */
    private function assertResultType(Method $method, mixed $result): bool
    {
        if (!$result instanceof Message) {
            $type = \get_debug_type($result);

            throw new \BadFunctionCallException(
                \sprintf(self::ERROR_METHOD_RETURN, $method->name, Message::class, $type),
            );
        }

        return true;
    }

    /**
     * Converts the input from the GRPC service method to the Message object.
     * @throws InvokeException
     */
    private function makeInput(Method $method, ?string $body): Message
    {
        try {
            $class = $method->inputType;
            \assert($this->assertInputType($method, $class));

            /** @psalm-suppress UnsafeInstantiation */
            $in = new $class();

            if ($body !== null) {
                $in->mergeFromString($body);
            }

            return $in;
        } catch (\Throwable $e) {
            throw InvokeException::create($e->getMessage(), StatusCode::INTERNAL, $e);
        }
    }

    /**
     * Checks that the input of the GRPC service method contains the
     * Message object.
     *
     * @param class-string $class
     * @throws \InvalidArgumentException
     */
    private function assertInputType(Method $method, string $class): bool
    {
        if (!\is_subclass_of($class, Message::class)) {
            throw new \InvalidArgumentException(
                \sprintf(self::ERROR_METHOD_IN_TYPE, $method->name, Message::class, $class),
            );
        }

        return true;
    }
}
