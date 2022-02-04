<?php

declare(strict_types=1);

namespace App\Consumer;

use App\Services\Import\CSV\ImportCSV;
use App\Services\Import\Logger\Logger;
use App\Services\Import\Savers\DoctrineSaver;
use App\Services\TempFilesManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class ImportSendConsumer implements ConsumerInterface
{
    public function __construct(
        private DoctrineSaver $saver,
        private Logger $logger,
        private TempFilesManager $filesManager,
    ) {
    }

    public function execute(AMQPMessage $msg): void
    {
        $id = (int) $msg->getBody();
        $status = $this->logger->getStatus($id);

        try {
            $import = ImportCSV::ImportFileByStatus($status, $this->saver);
            $this->logger->changeStatusToComplete($status, $import);
        } catch (Throwable) {
            $this->logger->changeStatusToFailed($status);
        } finally {
            if ($status->getRemovingFile()) {
                $this->filesManager->removeFile($status->getFileTmpName());
            }
        }
    }
}
