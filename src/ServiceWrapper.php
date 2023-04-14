<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC;

use Spiral\RoadRunner\GRPC\Exception\InvokeException;
use Spiral\RoadRunner\GRPC\Exception\NotFoundException;
use Spiral\RoadRunner\GRPC\Exception\ServiceException;

/**
 * Wraps handlers methods.
 */
final class ServiceWrapper
{
    /** @var non-empty-string */
    private string $name;

    private ServiceInterface $service;

    /** @var array<string, Method> */
    private array $methods;

    /**
     * @template T of ServiceInterface
     *
     * @param class-string<T> $interface Generated service interface.
     * @param T $service Must implement interface.
     */
    public function __construct(
        private readonly InvokerInterface $invoker,
        string $interface,
        ServiceInterface $service,
    ) {
        $this->configure($interface, $service);
    }

    /**
     * Get service name from class const `NAME`
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getService(): ServiceInterface
    {
        return $this->service;
    }

    /**
     * Get available service methods.
     *
     * @return Method[]
     */
    public function getMethods(): array
    {
        return \array_values($this->methods);
    }

    /**
     * Invoke given service method.
     *
     * @throws NotFoundException
     * @throws InvokeException
     */
    public function invoke(string $method, ContextInterface $context, ?string $input): string
    {
        if (! isset($this->methods[$method])) {
            throw NotFoundException::create("Method `{$method}` not found in service `{$this->name}`.");
        }

        return $this->invoker->invoke($this->service, $this->methods[$method], $context, $input);
    }

    /**
     * Configure service name and methods.
     *
     * @template T of ServiceInterface
     *
     * @param class-string<T> $interface Generated service interface.
     * @param T $service Must implement interface.
     *
     * @throws ServiceException
     */
    protected function configure(string $interface, ServiceInterface $service): void
    {
        try {
            $reflection = new \ReflectionClass($interface);

            if (! $reflection->hasConstant('NAME')) {
                $message = "Invalid service interface `{$interface}`, constant `NAME` not found.";
                throw ServiceException::create($message);
            }

            /** @var non-empty-string $name */
            $name = $reflection->getConstant('NAME');

            if (! \is_string($name)) {
                $message = "Constant `NAME` of service interface `{$interface}` must be a type of string";
                throw ServiceException::create($message);
            }

            $this->name = $name;
        } catch (\ReflectionException $e) {
            $message = "Invalid service interface `{$interface}`.";
            throw ServiceException::create($message, StatusCode::INTERNAL, $e);
        }

        if (! $service instanceof $interface) {
            throw ServiceException::create("Service handler does not implement `{$interface}`.");
        }

        $this->service = $service;

        // list of all available methods and their object types
        $this->methods = $this->fetchMethods($service);
    }

    /**
     * @return array<string, Method>
     */
    protected function fetchMethods(ServiceInterface $service): array
    {
        $reflection = new \ReflectionObject($service);

        $methods = [];
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (Method::match($method)) {
                $methods[$method->getName()] = Method::parse($method);
            }
        }

        return $methods;
    }
}
