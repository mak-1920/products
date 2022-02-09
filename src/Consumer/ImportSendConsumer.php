<?php

declare(strict_types=1);

namespace App\Consumer;

use App\Entity\ImportStatus;
use App\Services\Import\Import;
use App\Services\Import\Readers\ReaderInterface;
use App\Services\Import\Readers\StatusOfCSV\Reader;
use App\Services\Import\Savers\Doctrine\Saver;
use App\Services\Import\Status;
use App\Services\Import\Transform\Doctrine\Converter;
use App\Services\Import\Transform\Doctrine\Filter;
use App\Services\TempFilesManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Throwable;

class ImportSendConsumer implements ConsumerInterface
{
    public function __construct(
        private Filter $filter,
        private Converter $converter,
        private Saver $saver,
        private Status $status,
        private TempFilesManager $filesManager,
        private HubInterface $hub,
    ) {
    }

    public function execute(AMQPMessage $msg): void
    {
        $id = (int) $msg->getBody();
        $status = $this->status->getStatus($id);

        $this->tryImport($status);
        $this->tryRemoveFile($status);
        $this->sentResult($status);
    }

    private function tryImport(ImportStatus $status): void
    {
        $import = $this->getImport($status);

        $import->import();

        if ($import->isFailed()) {
            $this->status->changeStatusToFailed($status);
        } else {
            $this->status->changeStatusToComplete($status, $import);
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

    private function getImport(ImportStatus $status): Import
    {
        $import = new Import(
            $this->getReader($status),
            $this->saver,
            $this->converter,
            $this->filter,
        );

        return $import;
    }

    /**
     * @param ImportStatus $status
     *
     * @return ReaderInterface
     */
    private function getReader(ImportStatus $status): ReaderInterface
    {
        $reader = new Reader($status);

        return $reader;
    }
}
