<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use PHPUnit\Framework\TestCase;
use Service\Message;
use Spiral\RoadRunner\GRPC\Context;
use Spiral\RoadRunner\GRPC\Invoker;
use Spiral\RoadRunner\GRPC\Method;
use Spiral\RoadRunner\GRPC\Tests\Stub\TestService;

class InvokerTest extends TestCase
{
    public function testInvoke(): void
    {
        $s = new TestService();
        $m = Method::parse(new \ReflectionMethod($s, 'Echo'));

        $i = new Invoker();

        $out = $i->invoke($s, $m, new Context([]), $this->packMessage('hello'));

        $m = new Message();
        $m->mergeFromString($out);

        $this->assertSame('pong', $m->getMsg());
    }

    public function testInvokeError(): void
    {
        $this->expectException(\Spiral\RoadRunner\GRPC\Exception\InvokeException::class);

        $s = new TestService();
        $m = Method::parse(new \ReflectionMethod($s, 'Echo'));

        $i = new Invoker();

        $i->invoke($s, $m, new Context([]), 'invalid-message');
    }

    private function packMessage(string $message): string
    {
        $m = new Message();
        $m->setMsg($message);

        return $m->serializeToString();
    }
}
