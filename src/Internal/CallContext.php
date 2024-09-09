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
        public string $service,
        public string $method,
        public array $context,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public static function decode(string $payload): self
    {
        $data = Json::decode($payload);

        return new self(
            service: $data['service'],
            method: $data['method'],
            context: $data['context'],
        );
    }
}
