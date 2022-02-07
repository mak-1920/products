<?php

declare(strict_types=1);

namespace App\Services\Import\Logger;

use App\Entity\ImportStatus;
use App\Repository\ImportStatusRepository;
use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\CSV\ImportCSV;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Logger
{
    public function __construct(
        private ImportStatusRepository $repository,
        private MailerInterface $mailer,
    ) {
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
        $settings = array_pad($settings, count($filesInfo), CSVSettings::getDefaultInString());
        $ids = [];

        for ($i = 0; $i < count($filesInfo); ++$i) {
            $ids[] = $this->createStatus($filesInfo[$i], $settings[$i], $token);
        }

        return $ids;
    }

    /**
     * @param array{file: File, originalName: string} $fileInfo
     * @param string $settings
     * @param string $token
     *
     * @return int row's id in db
     */
    public function createStatus(array $fileInfo, string $settings, string $token): int
    {
        $status = new ImportStatus();

        $status->setStatus('STATUS_NEW');
        $status->setToken($token);
        $status->setFileOriginalName($fileInfo['originalName']);
        $status->setFileTmpName($fileInfo['file']->getRealPath());
        $status->setCsvSettings($settings);
        $status->setRemovingFile($fileInfo['isRemoving']);

        return $this->repository->addStatus($status);
    }

    /**
     * @param int $id
     *
     * @return ImportStatus
     */
    public function getStatus(int $id): ImportStatus
    {
        $status = $this->repository->find($id);

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

        $this->sendMail($status);
    }

    public function changeStatusToComplete(ImportStatus $status, ImportCSV $import): void
    {
        $this->repository->changeStatus(
            $status,
            true,
            [
                'success' => $import->getComplete(),
                'failed' => $import->getFailed(),
            ]
        );

        $this->sendMail($status);
    }

    /**
     * @param ImportStatus $status
     *
     * @return void
     */
    private function sendMail(ImportStatus $status): void
    {
        $mail = (new Email())
            ->from('products@prod.com')
            ->to('some-user@some.domain')
            ->subject('import')
            ->text((string) $status);

        $this->mailer->send($mail);
    }
}
