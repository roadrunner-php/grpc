<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use Google\Rpc\Status;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Service\Message;
use Service\TestInterface;
use Spiral\Goridge\Frame;
use Spiral\Goridge\RelayInterface;
use Spiral\RoadRunner\GRPC\Exception\ServiceException;
use Spiral\RoadRunner\GRPC\Server;
use Spiral\RoadRunner\GRPC\Tests\Stub\TestService;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\WorkerInterface;
use Spiral\RoadRunner\GRPC\InvokerInterface;

class ServerTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private Server $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->server = new Server();
        $this->server->registerService(TestInterface::class, new TestService());
    }

    public function testInvoke(): void
    {
        $relay = $this->createRelay(
            'ping',
            [
                'service' => 'service.Test',
                'method' => 'Echo',
                'context' => [],
            ]
        );

        $relay->shouldReceive('send')->once()->withArgs(function (Frame $frame) {
            return $frame->payload === '{}' . $this->packMessage('pong');
        });

        $this->server->serve(
            new Worker($relay)
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
            ]
        );

        $relay->shouldReceive('send')->once()->withArgs(function (Frame $frame) {
            $error = base64_decode(json_decode($frame->payload, true)['error']);

            return str_contains($error, 'Service `service.Test2` not found.');
        });

        $this->server->serve(
            new Worker($relay)
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
            ]
        );

        $relay->shouldReceive('send')->once()->withArgs(function (Frame $frame) {
            $error = base64_decode(json_decode($frame->payload, true)['error']);

            return str_contains($error, 'Method `Echo2` not found in service `service.Test`.');
        });

        $this->server->serve(
            new Worker($relay)
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
            ]
        );

        $relay->shouldReceive('send')->once()->withArgs(function (Frame $frame) {
            return $frame->payload === 'Just another exception';
        });

        $this->server->serve(
            new Worker($relay)
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

        $worker->shouldReceive('respond')->once()->withArgs(function (Payload $payload) {
            $headers = json_decode($payload->header, true);
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

    private function packMessage(string $message): string
    {
        $m = new Message();
        $m->setMsg($message);

        return $m->serializeToString();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        ob_end_clean();

        m::close();
    }

    protected function createRelay(string $body, array $header): RelayInterface
    {
        $body = $this->packMessage($body);
        $header = json_encode($header);

        $relay = m::mock(RelayInterface::class);
        $relay->shouldReceive('waitFrame')->once()->andReturn(
            new Frame($header . $body, [mb_strlen($header)])
        );

        $header = json_encode(['stop' => true]);
        $relay->shouldReceive('waitFrame')->once()->andReturn(
            new Frame($header, [mb_strlen($header)], Frame::CONTROL)
        );

        return $relay;
    }
}
