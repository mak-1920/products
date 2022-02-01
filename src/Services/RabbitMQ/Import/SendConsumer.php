<?php

declare(strict_types=1);

namespace App\Services\RabbitMQ\Import;

use App\Services\Import\CSV\ImportCSV;
use App\Services\Import\Savers\DoctrineSaver;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class SendConsumer implements ConsumerInterface
{
    public function __construct(
        private MessageSerializer $messageSerializer,
        private DoctrineSaver $saver,
    ) {
    }

    public function execute(AMQPMessage $msg): void
    {
        try {
            $data = $this->messageSerializer->deserialize($msg);
            $import = new ImportCSV(
                $data['files'],
                $data['settings'],
                $data['testmode'],
                $this->saver
            );
            $import->saveRequests();
        } catch (Throwable $exception) {
            echo $exception->getMessage().PHP_EOL;
        }
    }
}
