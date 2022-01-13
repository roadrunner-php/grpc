<?php

declare(strict_types=1);

class EchoClient extends \Grpc\BaseStub
{
    public function Ping(\Service\Message $message, $metadata = [], $options = [])
    {
        return $this->_simpleRequest(
            '/service.Echo/Ping',
            $message,
            ['\Service\Message', 'decode'],
            $metadata,
            $options
        );
    }
}
