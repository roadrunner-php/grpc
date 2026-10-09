<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use Service\Message;
use Service\TestInterface;
use Spiral\RoadRunner\GRPC\Context;
use Spiral\RoadRunner\GRPC\Invoker;
use Spiral\RoadRunner\GRPC\ServiceInterface;
use Spiral\RoadRunner\GRPC\ServiceWrapper;
use Spiral\RoadRunner\GRPC\Tests\Stub\TestService;
use Testo\Assert;
use Testo\Assert\ExpectException;
use Testo\Expect;
use Testo\Test;

#[Test]
final class ServiceWrapperTest implements ServiceInterface
{
    public function testName(): void
    {
        $w = new ServiceWrapper(
            new Invoker(),
            TestInterface::class,
            new TestService(),
        );

        Assert::same($w->getName(), 'service.Test');
    }

    public function testService(): void
    {
        $w = new ServiceWrapper(
            new Invoker(),
            TestInterface::class,
            $t = new TestService(),
        );

        Assert::same($w->getService(), $t);
    }

    public function testMethods(): void
    {
        $w = new ServiceWrapper(
            new Invoker(),
            TestInterface::class,
            new TestService(),
        );

        Assert::count($w->getMethods(), 5);
    }

    public function testInvokeNotFound(): void
    {
        Expect::exception(\Spiral\RoadRunner\GRPC\Exception\NotFoundException::class);

        $w = new ServiceWrapper(
            new Invoker(),
            TestInterface::class,
            new TestService(),
        );

        $w->invoke('NotFound', new Context([]), '');
    }

    public function testInvoke(): void
    {
        $w = new ServiceWrapper(
            new Invoker(),
            TestInterface::class,
            new TestService(),
        );

        $out = $w->invoke('Echo', new Context([]), $this->packMessage('hello world'));

        $m = new Message();
        $m->mergeFromString($out);

        Assert::same($m->getMsg(), 'pong');
    }

    #[ExpectException(\Spiral\RoadRunner\GRPC\Exception\ServiceException::class)]
    public function testNotImplemented(): void
    {
        $w = new ServiceWrapper(
            new Invoker(),
            TestInterface::class,
            $this,
        );
    }

    #[ExpectException(\Spiral\RoadRunner\GRPC\Exception\ServiceException::class)]
    public function testInvalidInterface(): void
    {
        $w = new ServiceWrapper(
            new Invoker(),
            InvalidInterface::class,
            $this,
        );
    }

    #[ExpectException(\Spiral\RoadRunner\GRPC\Exception\ServiceException::class)]
    public function testInvalidInterface2(): void
    {
        $w = new ServiceWrapper(
            new Invoker(),
            'NotFound',
            $this,
        );
    }

    private function packMessage(string $message): string
    {
        $m = new Message();
        $m->setMsg($message);

        return $m->serializeToString();
    }
}
