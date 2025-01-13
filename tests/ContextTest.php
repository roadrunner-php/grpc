<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\RoadRunner\GRPC\Context;
use Spiral\RoadRunner\GRPC\ResponseHeaders;
use Spiral\RoadRunner\GRPC\ResponseTrailers;

class ContextTest extends TestCase
{
    public function testGetValue(): void
    {
        $ctx = new Context([
            'key' => ['value'],
        ]);

        $this->assertSame(['value'], $ctx->getValue('key'));
    }

    public function testGetNullValue(): void
    {
        $ctx = new Context([
            'key' => ['value'],
        ]);

        $this->assertSame(null, $ctx->getValue('other'));
    }

    public function testGetValues(): void
    {
        $ctx = new Context([
            'key' => ['value'],
        ]);

        $this->assertSame([
            'key' => ['value'],
        ], $ctx->getValues());
    }

    public function testWithValue(): void
    {
        $ctx = new Context([
            'key' => ['value'],
        ]);

        $this->assertSame(['value'], $ctx->getValue('key'));

        $ctx2 = $ctx->withValue('new', 'another')->withValue('key', ['value2']);

        $this->assertSame(['value'], $ctx->getValue('key'));
        $this->assertSame(null, $ctx->getValue('new'));

        $this->assertSame(['value2'], $ctx2->getValue('key'));
        $this->assertSame('another', $ctx2->getValue('new'));
    }

    public function testGetOutgoingHeader(): void
    {
        $outgoingHeaders = [
            'Set-Cookie' => 'foobar',
        ];
        $ctx = new Context([ResponseHeaders::class => new ResponseHeaders($outgoingHeaders)]);

        $this->assertSame($outgoingHeaders['Set-Cookie'], $ctx->getValue(ResponseHeaders::class)->get('Set-Cookie'));
        $this->assertNull($ctx->getValue(ResponseHeaders::class)->get('not-existing'));
    }

    public function testGetOutgoingHeaders(): void
    {
        $outgoingHeaders = new ResponseHeaders([
            'Set-Cookie' => 'foobar',
        ]);
        $ctx = new Context([ResponseHeaders::class => $outgoingHeaders]);
        $this->assertSame($outgoingHeaders, $ctx->getValue(ResponseHeaders::class));
    }

    public function testGetOutgoingTrailer(): void
    {
        $outgoingTrailers = [
            'X-Some-Trailer' => 'foobar',
        ];
        $ctx = new Context([ResponseTrailers::class => new ResponseTrailers($outgoingTrailers)]);

        $this->assertSame($outgoingTrailers['X-Some-Trailer'], $ctx->getValue(ResponseTrailers::class)->get('X-Some-Trailer'));
        $this->assertNull($ctx->getValue(ResponseTrailers::class)->get('not-existing'));
    }

    public function testGetOutgoingTrailers(): void
    {
        $outgoingTrailers = new ResponseTrailers([
            'X-Some-Trailer' => 'foobar',
        ]);
        $ctx = new Context([ResponseTrailers::class => $outgoingTrailers]);
        $this->assertSame($outgoingTrailers, $ctx->getValue(ResponseTrailers::class));
    }
}
