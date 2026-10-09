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

    public function testGetValueDefault(): void
    {
        $ctx = new Context([]);

        Assert::same($ctx->getValue('missing', 'default'), 'default');
    }

    public function testWithValueKeepsOriginalImmutable(): void
    {
        $ctx = new Context(['key' => 'value']);

        $ctx2 = $ctx->withValue('key', 'changed');

        Assert::notSame($ctx2, $ctx);
        Assert::same($ctx->getValues(), ['key' => 'value']);
        Assert::same($ctx2->getValues(), ['key' => 'changed']);
    }

    public function testArrayAccess(): void
    {
        $ctx = new Context(['key' => 'value', 'nullable' => null]);

        Assert::true(isset($ctx['key']));
        Assert::true($ctx->offsetExists('nullable'));
        Assert::false(isset($ctx['missing']));
        Assert::same($ctx['key'], 'value');
        Assert::null($ctx['missing']);

        $ctx['new'] = 'another';
        unset($ctx['key']);

        Assert::same($ctx->getValues(), ['nullable' => null, 'new' => 'another']);
    }

    public function testIterateAndCount(): void
    {
        $values = ['first' => 1, 'second' => [2]];
        $ctx = new Context($values);

        Assert::same(\iterator_to_array($ctx), $values);
        Assert::count($ctx, 2);
    }
}
