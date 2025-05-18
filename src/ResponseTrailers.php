<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC;

use Spiral\RoadRunner\GRPC\Internal\Json;

/**
 * @psalm-type THeaderKey = non-empty-string
 * @psalm-type THeaderValue = string
 * @implements \IteratorAggregate<THeaderKey, string>
 */
final class ResponseTrailers implements \IteratorAggregate, \Countable
{
    /**
     * @var array<THeaderKey, THeaderValue>
     */
    private array $trailers = [];

    /**
     * @param iterable<THeaderKey, THeaderValue> $trailers
     */
    public function __construct(iterable $trailers = [])
    {
        foreach ($trailers as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @param THeaderKey $key
     * @param THeaderValue $value
     */
    public function set(string $key, string $value): void
    {
        $this->trailers[$key] = $value;
    }

    /**
     * @param THeaderKey $key
     * @return THeaderValue|null
     */
    public function get(string $key, ?string $default = null): ?string
    {
        return $this->trailers[$key] ?? $default;
    }

    #[\Override]
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->trailers);
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->trailers);
    }

    /**
     * @throws \JsonException
     */
    public function packTrailers(): string
    {
        // If an empty array is serialized, it is cast to the string "[]"
        // instead of object string "{}"
        if ($this->trailers === []) {
            return '{}';
        }

        return Json::encode($this->trailers);
    }
}
