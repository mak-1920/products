<?php

declare(strict_types=1);

namespace App\Services\Import;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

class TempFilesManager
{
    private const TMP_DIR = __DIR__.'/../../../public/tmp';

    /**
     * @param UploadedFile[] $files
     *
     * @return array<array{file: File, originalName: string}>
     */
    public function saveFiles(array $files): array
    {
        $movingFiles = [];

        foreach ($files as $file) {
            $movingFiles[] = $this->saveFile($file);
        }

        return $movingFiles;
    }

    /**
     * @param UploadedFile $file
     *
     * @return array{file: File, originalName: string}
     */
    public function saveFile(UploadedFile $file): array
    {
        try {
            $removing = true;
            $newFile = $file->move(self::TMP_DIR.'/');
        } catch (Throwable) {
            $removing = false;
            $newFile = $file;
        }

        return [
            'file' => $newFile,
            'originalName' => $file->getClientOriginalName(),
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
