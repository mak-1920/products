<?php

declare(strict_types=1);

namespace App\Consumer;

use App\Entity\ImportStatus;
use App\Services\Import\CSV\ImportCSV;
use App\Services\Import\Logger\Logger;
use App\Services\Import\Savers\DoctrineSaver;
use App\Services\TempFilesManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Throwable;

class ImportSendConsumer implements ConsumerInterface
{
    public function __construct(
        private DoctrineSaver $saver,
        private Logger $logger,
        private TempFilesManager $filesManager,
        private HubInterface $hub,
    ) {
    }

    public function execute(AMQPMessage $msg): void
    {
        $id = (int) $msg->getBody();
        $status = $this->logger->getStatus($id);

        $this->tryImport($status);
        $this->tryRemoveFile($status);
        $this->sentResult($status);
    }

    private function tryImport(ImportStatus $status): void
    {
        try {
            $import = ImportCSV::ImportFileByStatus($status, $this->saver);
            $this->logger->changeStatusToComplete($status, $import);
        } catch (Throwable) {
            $this->logger->changeStatusToFailed($status);
        }
    }

    private function tryRemoveFile(ImportStatus $status): void
    {
        try {
            if ($status->getRemovingFile()) {
                $this->filesManager->removeFile($status->getFileTmpName());
            }
        } catch (Throwable) {
            echo 'File '.$status->getFileTmpName().' not removed';
        }
    }

    private function sentResult(ImportStatus $status): void
    {
        $update = new Update(
            '/import/send/'.$status->getToken(),
            $status->toJson()
        );

        if ($status->isSent()) {
            $this->hub->publish($update);
        }
    }
}
