<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC;

/**
 * Carries information about call context, client information and metadata.
 *
 * @psalm-type TValues = array<string, mixed>
 */
interface ContextInterface
{
    /**
     * Create context with new value.
     *
     * @param non-empty-string $key
     * @return $this
     */
    public function withValue(string $key, mixed $value): self;

    /**
     * Get context value or return null.
     *
     * @param non-empty-string $key
     */
    public function getValue(string $key): mixed;

    /**
     * Return all context values.
     *
     * @return TValues
     */
    public function getValues(): array;
}
