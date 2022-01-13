<?php

/**
 * Sample GRPC PHP server.
 */

use Service\EchoInterface;
use Spiral\RoadRunner\GRPC\Server;
use Spiral\RoadRunner\Worker;

require __DIR__ . '/vendor/autoload.php';

$server = new Server(null, [
    'debug' => false, // optional (default: false)
]);

$server->registerService(EchoInterface::class, new EchoService());

$server->serve(Worker::create());
