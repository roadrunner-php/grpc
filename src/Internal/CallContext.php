<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Internal;

use Spiral\RoadRunner\GRPC\ServiceInterface;

/**
 * @internal
 * @psalm-internal Spiral\RoadRunner\GRPC
 */
final class CallContext
{
    /**
     * @param class-string<ServiceInterface> $service
     * @param non-empty-string $method
     * @param array<string, array<string>> $context
     */
    public function __construct(
        public readonly string $service,
        public readonly string $method,
        public readonly array $context,
    ) {}

    /**
     * @throws \JsonException
     */
    public static function decode(string $payload): self
    {
        /**
         * @psalm-var array{
         *  service: class-string<ServiceInterface>,
         *  method:  non-empty-string,
         *  context: array<string, array<string>>
         * } $data
         */
        $data = Json::decode($payload);

        return new self(
            service: $data['service'],
            method: $data['method'],
            context: $data['context'],
        );
    }
}
