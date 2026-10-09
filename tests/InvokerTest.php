<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use Service\Message;
use Spiral\RoadRunner\GRPC\Context;
use Spiral\RoadRunner\GRPC\Invoker;
use Spiral\RoadRunner\GRPC\Method;
use Spiral\RoadRunner\GRPC\Tests\Stub\TestService;
use Testo\Assert;
use Testo\Expect;
use Testo\Test;

#[Test]
final class InvokerTest
{
    public function testInvoke(): void
    {
        $s = new TestService();
        $m = Method::parse(new \ReflectionMethod($s, 'Echo'));

        $i = new Invoker();

        $out = $i->invoke($s, $m, new Context([]), $this->packMessage('hello'));

        $m = new Message();
        $m->mergeFromString($out);

        Assert::same($m->getMsg(), 'pong');
    }

    public function testInvokeWithInputMessage(): void
    {
        $s = new TestService();
        $m = Method::parse(new \ReflectionMethod($s, 'Echo'));

        $i = new Invoker();

        $out = $i->invoke($s, $m, new Context([]), $this->createMessage('hello'));

        $m = new Message();
        $m->mergeFromString($out);

        Assert::same($m->getMsg(), 'pong');
    }

    public function testInvokeError(): void
    {
        Expect::exception(\Spiral\RoadRunner\GRPC\Exception\InvokeException::class);

        $s = new TestService();
        $m = Method::parse(new \ReflectionMethod($s, 'Echo'));

        $i = new Invoker();

        $i->invoke($s, $m, new Context([]), 'invalid-message');
    }

    private function packMessage(string $message): string
    {
        return $this->createMessage($message)->serializeToString();
    }

    private function createMessage(string $message): Message
    {
        $m = new Message();
        $m->setMsg($message);

        return $m;
    }
}
