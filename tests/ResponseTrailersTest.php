<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use Spiral\RoadRunner\GRPC\ResponseTrailers;
use Testo\Assert;
use Testo\Test;

#[Test]
final class ResponseTrailersTest
{
    public function testSetOverridesValue(): void
    {
        $trailers = new ResponseTrailers(['foo' => 'bar']);

        $trailers->set('foo', 'baz');

        Assert::same($trailers->get('foo'), 'baz');
    }

    public function testGetDefault(): void
    {
        $trailers = new ResponseTrailers();

        Assert::same($trailers->get('missing', 'default'), 'default');
    }

    public function testIterateAndCount(): void
    {
        $values = ['foo' => 'bar', 'baz' => 'qux'];

        $trailers = new ResponseTrailers($values);

        Assert::same(\iterator_to_array($trailers), $values);
        Assert::count($trailers, 2);
    }

    public function testPackEmpty(): void
    {
        $trailers = new ResponseTrailers();

        Assert::same($trailers->packTrailers(), '{}');
    }

    public function testPack(): void
    {
        $trailers = new ResponseTrailers(['foo' => 'bar', 'baz' => 'qux']);

        Assert::same($trailers->packTrailers(), '{"foo":"bar","baz":"qux"}');
    }
}
