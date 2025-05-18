<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC;

use Spiral\RoadRunner\GRPC\Internal\Json;

/**
 * @psalm-type THeaderKey = non-empty-string
 * @psalm-type THeaderValue = string
 * @implements \IteratorAggregate<THeaderKey, string>
 */
final class ResponseHeaders implements \IteratorAggregate, \Countable
{
    /**
     * @var array<THeaderKey, THeaderValue>
     */
    private array $headers = [];

    /**
     * @param iterable<THeaderKey, THeaderValue> $headers
     */
    public function __construct(iterable $headers = [])
    {
        foreach ($headers as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @param THeaderKey $key
     * @param THeaderValue $value
     */
    public function set(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    /**
     * @param THeaderKey $key
     * @return THeaderValue|null
     */
    public function get(string $key, ?string $default = null): ?string
    {
        return $this->headers[$key] ?? $default;
    }

    #[\Override]
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->headers);
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->headers);
    }

    /**
     * @throws \JsonException
     */
    public function packHeaders(): string
    {
        // If an empty array is serialized, it is cast to the string "[]"
        // instead of object string "{}"
        if ($this->headers === []) {
            return '{}';
        }

        return Json::encode($this->headers);
    }
}
