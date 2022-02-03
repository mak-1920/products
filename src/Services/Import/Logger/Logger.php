<?php

declare(strict_types=1);

namespace App\Services\Import\Logger;

use App\Entity\ImportStatus;
use App\Repository\ImportStatusRepository;
use App\Services\Import\CSV\CSVSettings;
use App\Services\Import\CSV\ImportCSV;
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
     * @param array $data
     *
     * @return int[]
     */
    public function createStatuses(array $data): array
    {
        $files = $data['files'];
        $settings = array_pad($data['settings'], count($files), CSVSettings::getDefault());
        $ids = [];

        for ($i = 0; $i < count($files); ++$i) {
            $ids[] = $this->createStatus($files[$i], $settings[$i]);
        }

        return $ids;
    }

    /**
     * @param array{file: File, originalName: string} $fileInfo
     * @param CSVSettings $settings
     *
     * @return int row's id in db
     */
    public function createStatus(array $fileInfo, CSVSettings $settings): int
    {
        $status = new ImportStatus();

        $status->setStatus('STATUS_NEW');
        $status->setFileOriginalName($fileInfo['originalName']);
        $status->setFileTmpName($fileInfo['file']->getRealPath());
        $status->setCsvSettings((string) $settings);
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
        switch ($status->getStatus()) {
            case 'STATUS_IMPORTED':
                $text = $this->getTextForValid($status);
                break;
            case 'STATUS_FAILED':
                $text = $this->getTextForInvalid($status);
                break;
            default:
                return;
        }

        $mail = (new Email())
            ->from('products@prod.com')
            ->to('some-user@some.domain')
            ->subject('import')
            ->text($text);

        $this->mailer->send($mail);
    }

    /**
     * @param ImportStatus $status
     *
     * @return string
     */
    private function getTextForValid(ImportStatus $status): string
    {
        $text = 'Success import (id'.$status->getId().')'.PHP_EOL;
        $text .= 'File: '.$status->getFileOriginalName().PHP_EOL.PHP_EOL;
        $text .= 'Requests: '.count($status->getInvalidRows()) + count($status->getValidRows()).PHP_EOL;
        $text .= 'Count of valid rows: '.count($status->getValidRows()).PHP_EOL;
        $text .= 'Count of invalid rows: '.count($status->getInvalidRows()).PHP_EOL;
        if (count($status->getInvalidRows()) > 0) {
            $text .= PHP_EOL.'Invalid rows: '.PHP_EOL;
            foreach ($status->getInvalidRows() as $row) {
                $text .= implode(', ', $row).PHP_EOL;
            }
        }

        return $text;
    }

    /**
     * @param ImportStatus $status
     *
     * @return string
     */
    private function getTextForInvalid(ImportStatus $status): string
    {
        $settings = CSVSettings::fromString($status->getCsvSettings());

        $text = 'Failed import (id'.$status->getId().')'.PHP_EOL;
        $text .= 'File: '.$status->getFileOriginalName().PHP_EOL.PHP_EOL;
        $text .= 'Settings of CSV:'.PHP_EOL;
        $text .= 'Delimiter: '.$settings->getDelimiter().PHP_EOL;
        $text .= 'Enclosure: '.$settings->getEnclosure().PHP_EOL;
        $text .= 'Escape: '.$settings->getEscape().PHP_EOL;
        $text .= 'Have header: '.$settings->getHaveHeader();

        return $text;
    }
}
