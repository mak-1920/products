<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\Logger\Logger;
use App\Services\TempFilesManager;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Sender
{
    public function __construct(
        private Logger $logger,
        private TempFilesManager $filesManager,
        private ProducerInterface $producer,
    ) {
    }

    /**
     * @param UploadedFile[]|string[] $files
     * @param string[] $settings
     * @param string $token
     *
     * @return int[] ids of requests
     */
    public function send(array $files, array $settings, string $token): array
    {
        if (0 === count($files)) {
            return [];
        }

        $filesInfo = $this->saveFilesAndGetInfo($files);
        $ids = $this->logger->createStatuses($filesInfo, $settings, $token);
        $this->sendIDs($ids);

        return $ids;
    }

    /**
     * @param UploadedFile[]|string[] $files
     *
     * @return array{file: File, originalName: string, isRemoving: bool}
     */
    private function saveFilesAndGetInfo(array $files): array
    {
        $filesInfo = $this->filesManager->saveFilesAndGetInfo($files);

        return $filesInfo;
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
