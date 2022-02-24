<?php

declare(strict_types=1);

namespace App\Services\Import\Senders;

use Symfony\Component\HttpFoundation\File\File;

interface SenderInterface
{
    /**
     * @param array<array{file: File, originalName: string, isRemoving: bool}> $files
     * @param string[] $settings
     * @param string $token
     *
     * @return int[] ids of requests
     */
    public function send(array $files, array $settings, string $token): array;
}
