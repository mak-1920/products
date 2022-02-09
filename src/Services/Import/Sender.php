<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\Loggers\FileLogger;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpFoundation\File\File;

class Sender
{
    public function __construct(
        private Status $status,
        private ProducerInterface $producer,
        FileLogger $fileLogger,
    ) {
        $this->status->addLogger($fileLogger);
    }

    /**
     * @param array{file: File, originalName: string, isRemoving: bool} $files
     * @param string[] $settings
     * @param string $token
     *
     * @return int[] ids of requests
     */
    public function send(array $files, array $settings, string $token): array
    {
        $ids = $this->status->createStatuses($files, $settings, $token);
        $this->sendIDs($ids);

        return $ids;
    }

    /**
     * @param int[] $ids
     *
     * @return void
     */
    private function sendIDs(array $ids): void
    {
        foreach ($ids as $id) {
            $this->producer->publish($id);
        }
    }
}
