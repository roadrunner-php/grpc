<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

$client = new \EchoClient('127.0.0.1:9001', [
    'credentials' => \Grpc\ChannelCredentials::createInsecure(),
]);

$message = new Service\Message();
$message->setMsg('PING');

[$response, $status] = $client->Ping($message)->wait();

echo $response->getMsg() . PHP_EOL;
