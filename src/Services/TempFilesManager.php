<?php

declare(strict_types=1);

namespace App\Services;

use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TempFilesManager
{
    private const TMP_DIR = __DIR__.'/../../public/tmp/';

    /**
     * @param UploadedFile[]|string[] $files
     *
     * @return array<array{file: File, originalName: string}>
     */
    public function saveFilesAndGetInfo(array $files): array
    {
        $movingFiles = [];

        foreach ($files as $file) {
            $movingFiles[] = $this->saveFileAndGetInfo($file);
        }

        return $movingFiles;
    }

    /**
     * @param UploadedFile|string $file
     *
     * @return array{file: File, originalName: string, isRemoving: bool}
     */
    #[ArrayShape([
        'file' => "\Symfony\Component\HttpFoundation\File\File",
        'originalName' => 'string',
        'isRemoving' => 'bool',
    ])]
    public function saveFileAndGetInfo(mixed $file): array
    {
        if ($file instanceof UploadedFile) {
            $removing = true;
            $newFile = $file->move(self::TMP_DIR);
            $originalName = $file->getClientOriginalName();
        } else {
            $removing = false;
            $newFile = new File($file);
            $originalName = $newFile->getBasename();
        }

        return [
            'file' => $newFile,
            'originalName' => $originalName,
            'isRemoving' => $removing,
        ];
    }

    /**
     * @param UploadedFile[] $files
     *
     * @return void
     */
    public function removeFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->removeFile($file->getPath());
        }
    }

    /**
     * @param string $filePath
     *
     * @return void
     */
    public function removeFile(string $filePath): void
    {
        unlink($filePath);
    }
}
