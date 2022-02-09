<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Entity\ImportStatus;
use App\Repository\ImportStatusRepository;
use App\Services\Import\Loggers\FileLogger;
use App\Services\Import\Loggers\LoggerCollection;
use App\Services\Import\Loggers\LoggerInterface;
use App\Services\Import\Loggers\MailLogger;
use App\Services\Import\Readers\CSV\Settings;
use Symfony\Component\HttpFoundation\File\File;

class Status
{
    public function __construct(
        private ImportStatusRepository $repository,
        private LoggerCollection $loggerCollection,
        MailLogger $mailLogger,
        FileLogger $fileLogger,
    ) {
        $this->loggerCollection->addLogger($mailLogger->setFrom('products@prod.com')->setTo('consumer@test.ru'));
        $this->loggerCollection->addLogger($fileLogger);
    }

    /**
     * @param array{file: File, originalName: string, isRemoving: bool} $filesInfo
     * @param string[] $settings
     * @param string $token
     *
     * @return int[]
     */
    public function createStatuses(array $filesInfo, array $settings, string $token): array
    {
        $settings = array_pad($settings, count($filesInfo), Settings::getDefaultInString());
        $ids = [];

        for ($i = 0; $i < count($filesInfo); ++$i) {
            $ids[] = $this->createStatus($filesInfo[$i], $settings[$i], $token);
        }

        return $ids;
    }

    /**
     * @param array{file: File, originalName: string, isRemoving: bool} $fileInfo
     * @param string $settings
     * @param string $token
     *
     * @return int row's id in db
     */
    public function createStatus(array $fileInfo, string $settings, string $token): int
    {
        $status = $this->setNewStatus($fileInfo, $settings, $token);

        $id = $this->repository->addStatus($status);
        $this->loggerCollection->created($status);

        return $id;
    }

    /**
     * @param array{file: File, originalName: string, isRemoving: bool} $fileInfo
     * @param string $settings
     * @param string $token
     *
     * @return ImportStatus
     */
    private function setNewStatus(array $fileInfo, string $settings, string $token): ImportStatus
    {
        $status = new ImportStatus();

        $status->setStatus('STATUS_NEW');
        $status->setToken($token);
        $status->setFileOriginalName($fileInfo['originalName']);
        $status->setFileTmpName($fileInfo['file']->getRealPath());
        $status->setCsvSettings($settings);
        $status->setRemovingFile($fileInfo['isRemoving']);

        return $status;
    }

    /**
     * @param int $id
     *
     * @return ImportStatus
     */
    public function getStatus(int $id): ImportStatus
    {
        $status = $this->repository->find($id);

        $this->loggerCollection->beforeProcessing($status);

        return $status;
    }

    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    public function changeStatusToFailed(ImportStatus $status): void
    {
        $this->repository->changeStatus($status, false);

        $this->loggerCollection->afterProcessing($status);
    }

    public function changeStatusToComplete(ImportStatus $status, Import $import): void
    {
        $this->repository->changeStatus(
            $status,
            true,
            [
                'success' => $import->getComplete(),
                'failed' => $import->getFailed(),
            ]
        );

        $this->loggerCollection->afterProcessing($status);
    }

    public function addLogger(LoggerInterface $logger): void
    {
        $this->loggerCollection->addLogger($logger);
    }
}
