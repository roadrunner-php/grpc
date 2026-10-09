<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use Service\Message;
use Spiral\RoadRunner\GRPC\ContextInterface;
use Spiral\RoadRunner\GRPC\Method;
use Spiral\RoadRunner\GRPC\Tests\Stub\TestService;
use Testo\Assert;
use Testo\Assert\ExpectException;
use Testo\Test;

final class MethodTest
{
    #[Test]
    #[ExpectException(\Spiral\RoadRunner\GRPC\Exception\GRPCException::class)]
    public function testInvalidParse(): void
    {
        Method::parse(new \ReflectionMethod($this, 'testInvalidParse'));
    }

    #[Test]
    public function testMatch(): void
    {
        $s = new TestService();
        Assert::true(Method::match(new \ReflectionMethod($s, 'Info')));
    }

    #[Test]
    public function testNoMatch(): void
    {
        Assert::false(Method::match(new \ReflectionMethod($this, 'tM')));
    }

    #[Test]
    public function testNoMatch2(): void
    {
        Assert::false(Method::match(new \ReflectionMethod($this, 'tM2')));
    }

    #[Test]
    public function testNoMatch3(): void
    {
        Assert::false(Method::match(new \ReflectionMethod($this, 'tM3')));
    }

    #[Test]
    public function testNoMatch4(): void
    {
        Assert::false(Method::match(new \ReflectionMethod($this, 'tM4')));
    }

    #[Test]
    public function testNoMatch5(): void
    {
        Assert::false(Method::match(new \ReflectionMethod($this, 'tM5')));
    }

    #[Test]
    public function testMethodName(): void
    {
        $s = new TestService();
        $m = Method::parse(new \ReflectionMethod($s, 'Info'));
        Assert::same($m->name, 'Info');
    }

    #[Test]
    public function testMethodInputType(): void
    {
        $s = new TestService();
        $m = Method::parse(new \ReflectionMethod($s, 'Info'));
        Assert::same($m->outputType, Message::class);
    }

    #[Test]
    public function testMethodOutputType(): void
    {
        $s = new TestService();
        $m = Method::parse(new \ReflectionMethod($s, 'Info'));
        Assert::same($m->outputType, Message::class);
    }

    public function tM(ContextInterface $context, TestService $input): Message {}

    public function tM2(ContextInterface $context, Message $input): TestService {}

    public function tM3(TestService $context, Message $input): TestService {}

    public function tM4(TestService $context, Message $input): Invalid {}

    public function tM5(TestService $context, Message $input): void {}
}
