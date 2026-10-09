<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use Spiral\RoadRunner\GRPC\ResponseHeaders;
use Testo\Assert;
use Testo\Test;

#[Test]
final class ResponseHeadersTest
{
    public function testSetOverridesValue(): void
    {
        $headers = new ResponseHeaders(['foo' => 'bar']);

        $headers->set('foo', 'baz');

        Assert::same($headers->get('foo'), 'baz');
    }

    public function testGetDefault(): void
    {
        $headers = new ResponseHeaders();

        Assert::same($headers->get('missing', 'default'), 'default');
    }

    public function testIterateAndCount(): void
    {
        $values = ['foo' => 'bar', 'baz' => 'qux'];

        $headers = new ResponseHeaders($values);

        Assert::same(\iterator_to_array($headers), $values);
        Assert::count($headers, 2);
    }

    public function testPackEmpty(): void
    {
        $headers = new ResponseHeaders();

        Assert::same($headers->packHeaders(), '{}');
    }

    public function testPack(): void
    {
        $headers = new ResponseHeaders(['foo' => 'bar', 'baz' => 'qux']);

        Assert::same($headers->packHeaders(), '{"foo":"bar","baz":"qux"}');
    }
}
