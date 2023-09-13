<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC;

use Google\Protobuf\Internal\Message;
use Spiral\RoadRunner\GRPC\Exception\GRPCException;

/**
 * Method carry information about one specific RPC method, it's input and return types. Provides ability to detect
 * GRPC methods based on given class declaration.
 */
final class Method
{
    private const ERROR_PARAMS_COUNT =
        'The GRPC method %s can only contain 2 parameters (input and output), but ' .
        'signature contains an %d parameters';

    private const ERROR_PARAM_UNION_TYPE =
        'Parameter $%s of the GRPC method %s cannot be declared using union type';

    private const ERROR_PARAM_CONTEXT_TYPE =
        'The first parameter $%s of the GRPC method %s can only take an instance of %s';

    private const ERROR_PARAM_INPUT_TYPE =
        'The second (input) parameter $%s of the GRPC method %s can only take ' .
        'an instance of %s, but type %s is indicated';

    private const ERROR_RETURN_UNION_TYPE =
        'Return type of the GRPC method %s cannot be declared using union type';

    private const ERROR_RETURN_TYPE =
        'Return type of the GRPC method %s must return ' .
        'an instance of %s, but type %s is indicated';

    private const ERROR_INVALID_GRPC_METHOD = 'Method %s is not valid GRPC method.';

    /**
     * @param non-empty-string $name
     * @param class-string<Message> $inputType
     * @param class-string<Message> $outputType
     */
    private function __construct(
        public readonly string $name,
        public readonly string $inputType,
        public readonly string $outputType,
    ) {
    }

    /**
     * @deprecated Use {Method->name} property instead.
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @deprecated Use {Method->inputType} property instead.
     * @return class-string<Message>
     */
    public function getInputType(): string
    {
        return $this->inputType;
    }

    /**
     * @deprecated Use {Method->outputType} property instead.
     * @return class-string<Message>
     */
    public function getOutputType(): string
    {
        return $this->outputType;
    }

    /**
     * Returns true if method signature matches.
     */
    public static function match(\ReflectionMethod $method): bool
    {
        try {
            self::assertMethodSignature($method);
        } catch (\Throwable) {
            return false;
        }

        return true;
    }

    /**
     * @throws \ReflectionException
     */
    private static function assertContextParameter(\ReflectionMethod $method, \ReflectionParameter $context): void
    {
        $type = $context->getType();

        // When the type is not specified, it means that it is declared as
        // a "mixed" type, which is a valid case
        if ($type !== null) {
            if (!$type instanceof \ReflectionNamedType) {
                $message = \sprintf(self::ERROR_PARAM_UNION_TYPE, $context->getName(), $method->getName());
                throw new \DomainException($message, 0x02);
            }

            // If the type is not declared as a generic "mixed" or "object",
            // then it can only be a type that implements ContextInterface.
            if (!\in_array($type->getName(), ['mixed', 'object'], true)) {
                /** @psalm-suppress ArgumentTypeCoercion */
                $isContextImplementedType = !$type->isBuiltin()
                    && (new \ReflectionClass($type->getName()))->implementsInterface(ContextInterface::class);

                // Checking that the signature can accept the context.
                //
                // TODO If the type is any other implementation of the
                //      Spiral\RoadRunner\GRPC\ContextInterface other than
                //      class Spiral\RoadRunner\GRPC\Context, it may cause an error.
                //      It might make sense to check for such cases?
                if (!$isContextImplementedType) {
                    $message = \vsprintf(self::ERROR_PARAM_CONTEXT_TYPE, [
                        $context->getName(),
                        $method->getName(),
                        ContextInterface::class,
                    ]);

                    throw new \DomainException($message, 0x03);
                }
            }
        }
    }

    /**
     * @throws \ReflectionException
     */
    private static function assertInputParameter(\ReflectionMethod $method, \ReflectionParameter $input): void
    {
        $type = $input->getType();

        // Parameter type cannot be omitted ("mixed")
        if ($type === null) {
            $message = \vsprintf(self::ERROR_PARAM_INPUT_TYPE, [
                $input->getName(),
                $method->getName(),
                Message::class,
                'mixed',
            ]);

            throw new \DomainException($message, 0x04);
        }

        // Parameter type cannot be declared as singular non-named type
        if (!$type instanceof \ReflectionNamedType) {
            $message = \sprintf(self::ERROR_PARAM_UNION_TYPE, $input->getName(), $method->getName());
            throw new \DomainException($message, 0x05);
        }

        /** @psalm-suppress ArgumentTypeCoercion */
        $isProtobufMessageType = !$type->isBuiltin()
            && (new \ReflectionClass($type->getName()))
                ->isSubclassOf(Message::class);

        if (!$isProtobufMessageType) {
            $message = \vsprintf(self::ERROR_PARAM_INPUT_TYPE, [
                $input->getName(),
                $method->getName(),
                Message::class,
                $type->getName(),
            ]);
            throw new \DomainException($message, 0x06);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private static function assertOutputReturnType(\ReflectionMethod $method): void
    {
        $type = $method->getReturnType();

        // Return type cannot be omitted ("mixed")
        if ($type === null) {
            $message = \sprintf(self::ERROR_RETURN_TYPE, $method->getName(), Message::class, 'mixed');
            throw new \DomainException($message, 0x07);
        }

        // Return type cannot be declared as singular non-named type
        if (!$type instanceof \ReflectionNamedType) {
            $message = \sprintf(self::ERROR_RETURN_UNION_TYPE, $method->getName());
            throw new \DomainException($message, 0x08);
        }

        /** @psalm-suppress ArgumentTypeCoercion */
        $isProtobufMessageType = !$type->isBuiltin()
            && (new \ReflectionClass($type->getName()))->isSubclassOf(Message::class);

        if (!$isProtobufMessageType) {
            $message = \sprintf(self::ERROR_RETURN_TYPE, $method->getName(), Message::class, $type->getName());
            throw new \DomainException($message, 0x09);
        }
    }

    /**
     * @throws \ReflectionException
     * @throws \DomainException
     */
    private static function assertMethodSignature(\ReflectionMethod $method): void
    {
        // Check that there are only two parameters
        if ($method->getNumberOfParameters() !== 2) {
            $message = \sprintf(self::ERROR_PARAMS_COUNT, $method->getName(), $method->getNumberOfParameters());
            throw new \DomainException($message, 0x01);
        }

        /**
         * @var array{
         *     0: \ReflectionParameter,
         *     1: \ReflectionParameter
         * } $params
         */
        $params = $method->getParameters();

        [$context, $input] = $params;

        // The first parameter can only take a context object
        self::assertContextParameter($method, $context);

        // The second argument can only be a subtype of the Google\Protobuf\Internal\Message class
        self::assertInputParameter($method, $input);

        // The return type must be declared as a Google\Protobuf\Internal\Message class
        self::assertOutputReturnType($method);
    }

    /**
     * Creates a new {@see Method} object from a {@see \ReflectionMethod} object.
     */
    public static function parse(\ReflectionMethod $method): Method
    {
        try {
            self::assertMethodSignature($method);
        } catch (\Throwable $e) {
            $message = \sprintf(self::ERROR_INVALID_GRPC_METHOD, $method->getName());
            throw GRPCException::create($message, StatusCode::INTERNAL, $e);
        }

        [, $input] = $method->getParameters();

        /** @var \ReflectionNamedType $inputType */
        $inputType = $input->getType();

        /** @var \ReflectionNamedType $returnType */
        $returnType = $method->getReturnType();

        /** @psalm-suppress ArgumentTypeCoercion */
        return new self($method->getName(), $inputType->getName(), $returnType->getName());
    }
}
