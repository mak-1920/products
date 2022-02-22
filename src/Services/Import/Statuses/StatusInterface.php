<?php

declare(strict_types=1);

namespace App\Services\Import\Statuses;

use App\Entity\ImportStatus;
use App\Services\Import\Import;
use Symfony\Component\HttpFoundation\File\File;

interface StatusInterface
{
    /**
     * @param array{file: File, originalName: string, isRemoving: bool} $filesInfo
     * @param string[] $settings
     * @param string $token
     *
     * @return int[]
     */
    public function createStatuses(array $filesInfo, array $settings, string $token): array;

    /**
     * @param array{file: File, originalName: string, isRemoving: bool} $fileInfo
     * @param string $settings
     * @param string $token
     *
     * @return int row's id in db
     */
    public function createStatus(array $fileInfo, string $settings, string $token): int;

    /**
     * @param int $id
     *
     * @return ImportStatus
     */
    public function getStatus(int $id): ImportStatus;

    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    public function changeStatusToFailed(ImportStatus $status): void;

    /**
     * @param ImportStatus $status
     * @param Import $import
     *
     * @return void
     */
    public function changeStatusToComplete(ImportStatus $status, Import $import): void;
}
