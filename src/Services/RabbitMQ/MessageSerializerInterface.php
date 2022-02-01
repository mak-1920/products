<?php

namespace App\Services\RabbitMQ;

use PhpAmqpLib\Message\AMQPMessage;

interface MessageSerializerInterface
{
    public function serialize(array $data): string;

    public function deserialize(AMQPMessage $msg): array;
}
