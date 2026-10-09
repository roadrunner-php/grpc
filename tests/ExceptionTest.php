<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use Service\Message;
use Spiral\RoadRunner\GRPC\Exception\GRPCException;
use Spiral\RoadRunner\GRPC\Exception\InvokeException;
use Spiral\RoadRunner\GRPC\Exception\NotFoundException;
use Spiral\RoadRunner\GRPC\Exception\UnauthenticatedException;
use Spiral\RoadRunner\GRPC\Exception\UnimplementedException;
use Spiral\RoadRunner\GRPC\StatusCode;
use Testo\Assert;
use Testo\Test;

#[Test]
final class ExceptionTest
{
    public function testDefault(): void
    {
        $e = new GRPCException();
        Assert::same($e->getCode(), StatusCode::UNKNOWN);
    }

    public function testNotFound(): void
    {
        $e = new NotFoundException();
        Assert::same($e->getCode(), StatusCode::NOT_FOUND);
    }

    public function testInvoke(): void
    {
        $e = new InvokeException();
        Assert::same($e->getCode(), StatusCode::UNAVAILABLE);
    }

    public function testUnauthenticated(): void
    {
        $e = new UnauthenticatedException();
        Assert::same($e->getCode(), StatusCode::UNAUTHENTICATED);
    }

    public function testUnimplemented(): void
    {
        $e = new UnimplementedException();
        Assert::same($e->getCode(), StatusCode::UNIMPLEMENTED);
    }

    public function testCreate(): void
    {
        $previous = new \RuntimeException('cause');
        $details = [new Message()];

        $e = NotFoundException::create('missing', StatusCode::ABORTED, $previous, $details);

        Assert::instanceOf($e, NotFoundException::class);
        Assert::same($e->getMessage(), 'missing');
        Assert::same($e->getCode(), StatusCode::ABORTED);
        Assert::same($e->getPrevious(), $previous);
        Assert::same($e->getDetails(), $details);
    }

    public function testDetails(): void
    {
        $first = new Message();
        $second = new Message();
        $third = new Message();
        $e = new GRPCException('error', null, [$first]);

        $e->addDetails($second);

        Assert::same($e->getDetails(), [$first, $second]);

        $e->setDetails([$third]);

        Assert::same($e->getDetails(), [$third]);
    }
}
