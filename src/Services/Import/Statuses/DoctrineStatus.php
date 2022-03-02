<?php

declare(strict_types=1);

namespace App\Services\Import\Statuses;

use App\Entity\ImportStatus;
use App\Repository\ImportStatusRepository;
use App\Services\Cache\Memcached\MCStatus;
use App\Services\Import\Exceptions\Saver\SaverException;
use App\Services\Import\Exceptions\Status\UndefinedStatusIdException;
use App\Services\Import\Import;
use App\Services\Import\Loggers\LoggerCollection;
use App\Services\Import\Loggers\LoggerInterface;
use App\Services\Import\Readers\CSV\Settings;
use Symfony\Component\HttpFoundation\File\File;

class DoctrineStatus implements LoggingStatusInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private ImportStatusRepository $repository,
        private MCStatus $memcached,
    ) {
        $this->logger = new LoggerCollection();
    }

    /**
     * @param array<array{file: File, originalName: string, isRemoving: bool}> $filesInfo
     * @param string[] $settings
     * @param string $token
     *
     * @return int[]
     *
     * @throws SaverException
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
     *
     * @throws SaverException
     */
    public function createStatus(array $fileInfo, string $settings, string $token): int
    {
        $status = $this->setNewStatus($fileInfo, $settings, $token);

        $id = $this->repository->addStatus($status);
        $this->logger->created($status);

        return $id;
    }

    /**
     * @param array{file: File, originalName: string, isRemoving: bool} $fileInfo
     * @param string $settings
     * @param string $token
     *
     * @return ImportStatus
     *
     * @throws SaverException
     */
    private function setNewStatus(array $fileInfo, string $settings, string $token): ImportStatus
    {
        $status = new ImportStatus();
        $path = $fileInfo['file']->getRealPath();

        if (false === $path) {
            throw new SaverException('path was clear');
        }

        $status->setStatus('STATUS_NEW');
        $status->setToken($token);
        $status->setFileOriginalName($fileInfo['originalName']);
        $status->setFileTmpName($path);
        $status->setCsvSettings($settings);
        $status->setRemovingFile($fileInfo['isRemoving']);

        return $status;
    }

    /**
     * @param int $id
     *
     * @return ImportStatus
     *
     * @throws UndefinedStatusIdException
     */
    public function getStatus(int $id): ImportStatus
    {
        $status = $this->getStatusFromMemcached($id);
        if (false !== $status) {
            return $status;
        }

        $status = $this->getStatusFromDoctrine($id);

        $this->setStatusInMemcached($status);
        $this->logger->beforeProcessing($status);

        return $status;
    }

    /**
     * @param int $id
     *
     * @return ImportStatus|false
     */
    private function getStatusFromMemcached(int $id): ImportStatus|false
    {
        $status = $this->memcached->get($id);

        return $status ?? false;
    }

    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    private function setStatusInMemcached(ImportStatus $status): void
    {
        $this->memcached->set($status);
    }

    /**
     * @param int $id
     *
     * @return ImportStatus
     *
     * @throws UndefinedStatusIdException
     */
    private function getStatusFromDoctrine(int $id): ImportStatus
    {
        $status = $this->repository->find($id);

        if (is_null($status)) {
            throw new UndefinedStatusIdException('Undefined status id: '.$id);
        }

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

        $this->logger->afterProcessing($status);
    }

    /**
     * @param ImportStatus $status
     * @param Import $import
     *
     * @return void
     */
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

        $this->logger->afterProcessing($status);
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
