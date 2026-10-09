<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use Spiral\RoadRunner\GRPC\Context;
use Spiral\RoadRunner\GRPC\ResponseHeaders;
use Spiral\RoadRunner\GRPC\ResponseTrailers;
use Testo\Assert;
use Testo\Test;

#[Test]
final class ContextTest
{
    public function testGetValue(): void
    {
        $ctx = new Context([
            'key' => ['value'],
        ]);

        Assert::same($ctx->getValue('key'), ['value']);
    }

    public function testGetNullValue(): void
    {
        $ctx = new Context([
            'key' => ['value'],
        ]);

        Assert::same($ctx->getValue('other'), null);
    }

    public function testGetValues(): void
    {
        $ctx = new Context([
            'key' => ['value'],
        ]);

        Assert::same($ctx->getValues(), [
            'key' => ['value'],
        ]);
    }

    public function testWithValue(): void
    {
        $ctx = new Context([
            'key' => ['value'],
        ]);

        Assert::same($ctx->getValue('key'), ['value']);

        $ctx2 = $ctx->withValue('new', 'another')->withValue('key', ['value2']);

        Assert::same($ctx->getValue('key'), ['value']);
        Assert::same($ctx->getValue('new'), null);

        Assert::same($ctx2->getValue('key'), ['value2']);
        Assert::same($ctx2->getValue('new'), 'another');
    }

    public function testGetOutgoingHeader(): void
    {
        $outgoingHeaders = [
            'Set-Cookie' => 'foobar',
        ];
        $ctx = new Context([ResponseHeaders::class => new ResponseHeaders($outgoingHeaders)]);

        Assert::same($ctx->getValue(ResponseHeaders::class)->get('Set-Cookie'), $outgoingHeaders['Set-Cookie']);
        Assert::null($ctx->getValue(ResponseHeaders::class)->get('not-existing'));
    }

    public function testGetOutgoingHeaders(): void
    {
        $outgoingHeaders = new ResponseHeaders([
            'Set-Cookie' => 'foobar',
        ]);
        $ctx = new Context([ResponseHeaders::class => $outgoingHeaders]);
        Assert::same($ctx->getValue(ResponseHeaders::class), $outgoingHeaders);
    }

    public function testGetOutgoingTrailer(): void
    {
        $outgoingTrailers = [
            'X-Some-Trailer' => 'foobar',
        ];
        $ctx = new Context([ResponseTrailers::class => new ResponseTrailers($outgoingTrailers)]);

        Assert::same($ctx->getValue(ResponseTrailers::class)->get('X-Some-Trailer'), $outgoingTrailers['X-Some-Trailer']);
        Assert::null($ctx->getValue(ResponseTrailers::class)->get('not-existing'));
    }

    public function testGetOutgoingTrailers(): void
    {
        $outgoingTrailers = new ResponseTrailers([
            'X-Some-Trailer' => 'foobar',
        ]);
        $ctx = new Context([ResponseTrailers::class => $outgoingTrailers]);
        Assert::same($ctx->getValue(ResponseTrailers::class), $outgoingTrailers);
    }
}
