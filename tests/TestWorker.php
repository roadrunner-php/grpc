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
use Spiral\RoadRunner\Worker;

class TestWorker extends Worker
{
    private $t;
    private $sequence = [];
    private $pos      = 0;

    public function __construct(TestCase $t, array $sequence)
    {
        $this->t = $t;
        $this->sequence = $sequence;
    }

    public function done()
    {
        return $this->pos == count($this->sequence);
    }

    public function receive(&$header)
    {
        if (!isset($this->sequence[$this->pos])) {
            $header = null;
            return null;
        }

        $header = json_encode($this->sequence[$this->pos]['ctx']);

        return $this->sequence[$this->pos]['send'];
    }

    public function send(string $payload = null, string $header = null): void
    {
        $this->t->assertSame($this->sequence[$this->pos]['receive'], $payload);
        $this->pos++;
    }

    public function error(string $message): void
    {
        $this->t->assertSame($this->sequence[$this->pos]['error'], $message);
        $this->pos++;
    }
}
