<?php

namespace App\Services\RabbitMQ;

interface ProducerInterface
{
    public function send(string $msg): void;
}
