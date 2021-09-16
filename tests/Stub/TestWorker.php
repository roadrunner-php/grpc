<?php

/**
 * This file is part of RoadRunner package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests\Stub;

use PHPUnit\Framework\TestCase;
use Spiral\Goridge\Frame;
use Spiral\Goridge\RelayInterface;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\Worker;

class TestWorker extends Worker
{
    private TestCase $context;
    private int $pos = 0;
    private array $sequence = [];

    public function __construct(TestCase $context, array $sequence)
    {
        $this->context = $context;
        $this->sequence = $sequence;

        parent::__construct(new class implements RelayInterface {
            public function waitFrame(): Frame
            {
                throw new \LogicException(__METHOD__ . ' not implemented yet');
            }

            public function send(Frame $frame): void
            {
                throw new \LogicException(__METHOD__ . ' not implemented yet');
            }
        }, false);
    }

    public function done(): bool
    {
        return $this->pos == \count($this->sequence);
    }

    public function receive(&$header)
    {
        if (! isset($this->sequence[$this->pos])) {
            $header = null;

            return null;
        }

        $header = \json_encode($this->sequence[$this->pos]['ctx']);

        return $this->sequence[$this->pos]['send'];
    }

    public function respond(Payload $payload): void
    {
        $this->send($payload->body, $payload->header);
    }

    public function send(string $payload = null, string $header = null): void
    {
        $this->context->assertSame($this->sequence[$this->pos]['receive'], $payload);
        $this->pos++;
    }

    public function error(string $message): void
    {
        $this->context->assertSame($this->sequence[$this->pos]['error'], $message);
        $this->pos++;
    }
}
