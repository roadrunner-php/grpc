<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Internal;

/**
 * @internal Json is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\RoadRunner\GRPC
 */
final class Json
{
    /**
     * @var positive-int
     */
    public const DEFAULT_JSON_DEPTH = 512;

    /**
     * @var positive-int|0
     */
    public const DEFAULT_JSON_FLAGS = \JSON_THROW_ON_ERROR;

    /**
     * @throws \JsonException
     */
    public static function encode(mixed $payload): string
    {
        return \json_encode($payload, self::DEFAULT_JSON_FLAGS, self::DEFAULT_JSON_DEPTH);
    }

    /**
     * @throws \JsonException
     */
    public static function decode(string $payload): array
    {
        return (array)\json_decode($payload, true, self::DEFAULT_JSON_DEPTH, self::DEFAULT_JSON_FLAGS);
    }
}
