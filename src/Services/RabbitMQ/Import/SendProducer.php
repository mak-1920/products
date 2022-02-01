<?php

declare(strict_types=1);

namespace App\Services\RabbitMQ\Import;

use App\Services\RabbitMQ\ProducerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface as RabbitProducerInterface;

class SendProducer implements ProducerInterface
{
    public function __construct(
        private RabbitProducerInterface $producer,
    ) {
    }

    public function send(string $msg): void
    {
        $this->producer->publish($msg);
    }
}