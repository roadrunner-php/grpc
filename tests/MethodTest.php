<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use Service\EmptyMessage;
use Service\Message;
use Spiral\RoadRunner\GRPC\ContextInterface;
use Spiral\RoadRunner\GRPC\Exception\GRPCException;
use Spiral\RoadRunner\GRPC\Method;
use Spiral\RoadRunner\GRPC\StatusCode;
use Spiral\RoadRunner\GRPC\Tests\Stub\TestService;
use Testo\Assert;
use Testo\Assert\Api\ExpectedException;
use Testo\Assert\ExpectException;
use Testo\Data\DataSet;
use Testo\Expect;
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
        Assert::same($m->inputType, Message::class);
    }

    #[Test]
    public function testMethodOutputType(): void
    {
        $s = new TestService();
        $m = Method::parse(new \ReflectionMethod($s, 'Info'));
        Assert::same($m->outputType, Message::class);
    }

    #[Test]
    public function testDeprecatedGetters(): void
    {
        $m = Method::parse(new \ReflectionMethod(new TestService(), 'Ping'));

        Assert::same($m->getName(), 'Ping');
        Assert::same($m->getInputType(), EmptyMessage::class);
        Assert::same($m->getOutputType(), EmptyMessage::class);
    }

    #[Test]
    #[DataSet(['tUntypedContext'], 'untyped context')]
    #[DataSet(['tObjectContext'], 'object context')]
    public function testContextMayBeLooselyTyped(string $method): void
    {
        Assert::true(Method::match(new \ReflectionMethod($this, $method)));
    }

    #[Test]
    #[DataSet(['tUnionContext', 0x02], 'union context type')]
    #[DataSet(['tUntypedInput', 0x04], 'untyped input')]
    #[DataSet(['tUnionInput', 0x05], 'union input type')]
    #[DataSet(['tUntypedReturn', 0x07], 'untyped return')]
    #[DataSet(['tUnionReturn', 0x08], 'union return type')]
    public function testInvalidSignature(string $method, int $reason): never
    {
        Expect::exception(GRPCException::class)
            ->withCode(StatusCode::INTERNAL)
            ->withMessage("Method {$method} is not valid GRPC method.")
            ->withPrevious(\DomainException::class, static fn(ExpectedException $previous) => $previous->withCode($reason));

        Method::parse(new \ReflectionMethod($this, $method));
    }

    public function tUntypedContext($context, Message $input): Message {}

    public function tObjectContext(object $context, Message $input): Message {}

    public function tUnionContext(ContextInterface|string $context, Message $input): Message {}

    public function tUntypedInput(ContextInterface $context, $input): Message {}

    public function tUnionInput(ContextInterface $context, Message|string $input): Message {}

    public function tUntypedReturn(ContextInterface $context, Message $input) {}

    public function tUnionReturn(ContextInterface $context, Message $input): Message|string {}

    public function tM(ContextInterface $context, TestService $input): Message {}

    public function tM2(ContextInterface $context, Message $input): TestService {}

    public function tM3(TestService $context, Message $input): TestService {}

    public function tM4(TestService $context, Message $input): Invalid {}

    public function tM5(TestService $context, Message $input): void {}
}
