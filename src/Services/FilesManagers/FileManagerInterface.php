<?php

declare(strict_types=1);

namespace App\Services\FilesManagers;

use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileManagerInterface
{
    /**
     * @param UploadedFile[]|string[] $files
     *
     * @return array<array{file: File, originalName: string, isRemoving: bool}>
     */
    public function saveFilesAndGetInfo(array $files): array;

    /**
     * @param UploadedFile|string $file
     *
     * @return array{file: File, originalName: string, isRemoving: bool}
     */
    #[ArrayShape([
        'file' => File::class,
        'originalName' => 'string',
        'isRemoving' => 'bool',
    ])]
    public function saveFileAndGetInfo(mixed $file): array;

    /**
     * @param File[] $files
     *
     * @return void
     */
    public function removeFiles(array $files): void;

    /**
     * @param string $filePath
     *
     * @return void
     */
    public function removeFile(string $filePath): void;
}
