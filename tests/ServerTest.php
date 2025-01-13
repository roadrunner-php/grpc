<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use Google\Rpc\Status;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Service\DetailsMessageForException;
use Service\Message;
use Service\TestInterface;
use Spiral\Goridge\Frame;
use Spiral\Goridge\RelayInterface;
use Spiral\RoadRunner\GRPC\Exception\ServiceException;
use Spiral\RoadRunner\GRPC\InvokerInterface;
use Spiral\RoadRunner\GRPC\Server;
use Spiral\RoadRunner\GRPC\Tests\Stub\TestService;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\WorkerInterface;

class ServerTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private Server $server;
    private int $obLevel;

    public function testInvoke(): void
    {
        $relay = $this->createRelay(
            'ping',
            [
                'service' => 'service.Test',
                'method' => 'Echo',
                'context' => [],
            ],
        );

        $relay->shouldReceive('send')->once()->withArgs(function (Frame $frame) {
            return $frame->payload === '{}' . $this->packMessage('pong');
        });

        $this->server->serve(
            new Worker($relay),
        );
    }

    public function testNotFound(): void
    {
        $relay = $this->createRelay(
            'ping',
            [
                'service' => 'service.Test2',
                'method' => 'Echo',
                'context' => [],
            ],
        );

        $relay->shouldReceive('send')->once()->withArgs(static function (Frame $frame) {
            $error = \base64_decode(\json_decode($frame->payload, true)['error']);

            return \str_contains($error, 'Service `service.Test2` not found.');
        });

        $this->server->serve(
            new Worker($relay),
        );
    }

    public function testNotFound2(): void
    {
        $relay = $this->createRelay(
            'ping',
            [
                'service' => 'service.Test',
                'method' => 'Echo2',
                'context' => [],
            ],
        );

        $relay->shouldReceive('send')->once()->withArgs(static function (Frame $frame) {
            $error = \base64_decode(\json_decode($frame->payload, true)['error']);

            return \str_contains($error, 'Method `Echo2` not found in service `service.Test`.');
        });

        $this->server->serve(
            new Worker($relay),
        );
    }

    public function testServerDebugModeNotEnabled(): void
    {
        $relay = $this->createRelay(
            'regularException',
            [
                'service' => 'service.Test',
                'method' => 'Throw',
                'context' => [],
            ],
        );

        $relay->shouldReceive('send')->once()->withArgs(static function (Frame $frame) {
            return $frame->payload === 'Just another exception';
        });

        $this->server->serve(
            new Worker($relay),
        );
    }

    public function testExceptionDetails(): void
    {
        $error = new Message();
        $error->setMsg('Invalid sample id');

        $invoker = m::mock(InvokerInterface::class);
        $invoker->shouldReceive('invoke')->once()
            ->andThrow(new ServiceException('Sample endpoint error', 200, [$error]));

        $worker = m::mock(WorkerInterface::class);
        $worker->shouldReceive('waitPayload')->once()
            ->andReturn(new Payload(body: 'ping', header: '{"context": {}, "service": "service.Test", "method": "Throw"}'));

        $worker->shouldReceive('waitPayload')->once()->andReturnNull();

        $worker->shouldReceive('respond')->once()->withArgs(static function (Payload $payload) {
            $headers = \json_decode($payload->header, true);
            $status = new Status();
            $status->mergeFromString(\base64_decode($headers['error']));

            $message = $status->getDetails()->offsetGet(0);
            $message = $message->unpack();

            return $message instanceof Message && $message->getMsg() === 'Invalid sample id';
        });

        $server = new Server($invoker);
        $service = new TestService();

        $server->registerService(TestInterface::class, $service);
        $server->serve($worker);
    }

    public function testInvokeGrpcException(): void
    {
        $worker = m::mock(WorkerInterface::class);
        $worker->shouldReceive('waitPayload')
            ->times(2)
            ->andReturn(
                new Payload(
                    body: $this->packMessage('withDetailsAndHeaders'),
                    header: '{"context": {}, "service": "service.Test", "method": "Throw"}',
                ),
                null,
            );

        $worker->shouldReceive('respond')->once()
            ->withArgs(static function (Payload $payload) {
                $header = \json_decode($payload->header, true);

                $status = new Status();
                $status->mergeFromString(\base64_decode($header['error']));
                /** @var DetailsMessageForException $message */
                $message = $status->getDetails()->offsetGet(0)->unpack();

                $outgoingHeaders = \json_decode($header['headers'], true);
                $outgoingTrailers = \json_decode($header['trailers'], true);

                return $message instanceof DetailsMessageForException
                    && $message->getMessage() === 'details message'
                    && $outgoingHeaders === ['foo' => 'bar']
                    && $outgoingTrailers === ['baz' => 'bar'];
            });

        $this->server->serve($worker);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->obLevel = \ob_get_level();

        $this->server = new Server();
        $this->server->registerService(TestInterface::class, new TestService());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->obLevel < \ob_get_level() and \ob_end_clean();

        m::close();
    }

    protected function createRelay(string $body, array $header): RelayInterface
    {
        $body = $this->packMessage($body);
        $header = \json_encode($header);

        $relay = m::mock(RelayInterface::class);
        $relay->shouldReceive('waitFrame')->once()->andReturn(
            new Frame($header . $body, [\mb_strlen($header)]),
        );

        $header = \json_encode(['stop' => true]);
        $relay->shouldReceive('waitFrame')->once()->andReturn(
            new Frame($header, [\mb_strlen($header)], Frame::CONTROL),
        );

        return $relay;
    }

    private function packMessage(string $message): string
    {
        $m = new Message();
        $m->setMsg($message);

        return $m->serializeToString();
    }
}
