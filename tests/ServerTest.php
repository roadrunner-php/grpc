<?php

/**
 * This file is part of RoadRunner package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use PHPUnit\Framework\TestCase;
use Service\Message;
use Service\TestInterface;
use Spiral\GRPC\Server;
use Test\TestService;

class ServerTest extends TestCase
{
    public function testInvoke(): void
    {
        $s = new Server();
        $s->registerService(TestInterface::class, new TestService());

        $w = new TestWorker($this, [
            [
                'ctx'     => [
                    'service' => 'service.Test',
                    'method'  => 'Echo',
                    'context' => [],
                ],
                'send'    => $this->packMessage('hello world'),
                'receive' => $this->packMessage('hello world')
            ]
        ]);

        $s->serve($w);

        $this->assertTrue($w->done());
    }

    public function testNotFound(): void
    {
        $s = new Server();
        $s->registerService(TestInterface::class, new TestService());

        $w = new TestWorker($this, [
            [
                'ctx'   => [
                    'service' => 'service.Test2',
                    'method'  => 'Echo',
                    'context' => [],
                ],
                'send'  => $this->packMessage('hello world'),
                'error' => '5|:|Service `service.Test2` not found.'
            ]
        ]);

        $s->serve($w);

        $this->assertTrue($w->done());
    }

    public function testNotFound2(): void
    {
        $s = new Server();
        $s->registerService(TestInterface::class, new TestService());

        $w = new TestWorker($this, [
            [
                'ctx'   => [
                    'service' => 'service.Test',
                    'method'  => 'Echo2',
                    'context' => [],
                ],
                'send'  => $this->packMessage('hello world'),
                'error' => '5|:|Method `Echo2` not found in service `service.Test`.'
            ]
        ]);

        $s->serve($w);

        $this->assertTrue($w->done());
    }

    public function testServerDebugModeNotEnabled(): void
    {
        $s = new Server();
        $s->registerService(TestInterface::class, new TestService());

        $w = new TestWorker($this, [
            [
                'ctx'   => [
                    'service' => 'service.Test',
                    'method'  => 'Throw',
                    'context' => [],
                ],
                'send'  => $this->packMessage('regularException'),
                'error' => 'Just another exception'
            ]
        ]);

        $s->serve($w);

        $this->assertTrue($w->done());
    }

    private function packMessage(string $message): string
    {
        $m = new Message();
        $m->setMsg($message);

        return $m->serializeToString();
    }
}
