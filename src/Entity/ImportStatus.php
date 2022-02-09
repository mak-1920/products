<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ImportStatusRepository;
use App\Services\Import\Readers\CSV\Settings;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImportStatusRepository::class)]
class ImportStatus
{
    public const COMMAND_TOKEN_PREFIX = 'command_upload_';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(name: 'status', type: 'string', length: 20)]
    private string $status;

    #[ORM\Column(name: 'valid_rows', type: 'array', nullable: true)]
    private array $validRows = [];

    #[ORM\Column(name: 'invalid_rows', type: 'array', nullable: true)]
    private array $invalidRows = [];

    #[ORM\Column(name: 'file_name_original', type: 'string', length: 255)]
    private string $fileOriginalName;

    #[ORM\Column(name: 'file_name_tmp', type: 'string', length: 255)]
    private string $fileTmpName;

    #[ORM\Column(name: 'csv_settings', type: 'string', length: 20)]
    private string $csvSettings;

    #[ORM\Column(name: 'remove_file', type: 'boolean')]
    private bool $removingFile;

    #[ORM\Column(name: 'token', type: 'string', length: 255)]
    private string $token;

    public function __toString(): string
    {
        $text = $this->getStatusHeader();

        switch ($this->getStatus()) {
            case 'STATUS_IMPORTED':
                $text .= $this->getTextForValid();
                break;
            case 'STATUS_FAILED':
                $text .= $this->getTextForInvalid();
                break;
            default:
                break;
        }

        return $text;
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        $data = [
            'id' => $this->id,
            'file' => $this->fileOriginalName,
            'status' => $this->status,
            'complete' => $this->validRows,
            'failed' => $this->invalidRows,
            'settings' => $this->csvSettings,
        ];

        return json_encode($data);
    }

    public function isSent(): bool
    {
        return !str_contains($this->token, self::COMMAND_TOKEN_PREFIX);
    }

    /**
     * @return string
     */
    private function getStatusHeader(): string
    {
        $text = substr($this->status, stripos($this->status, '_') + 1);
        $text .= ' (id'.$this->id.')'.PHP_EOL;
        $text .= 'File: '.$this->fileOriginalName.PHP_EOL.PHP_EOL;

        return $text;
    }

    /**
     * @return string
     */
    private function getTextForValid(): string
    {
        $text = 'Requests: '.count($this->invalidRows) + count($this->validRows).PHP_EOL;
        $text .= 'Count of valid rows: '.count($this->validRows).PHP_EOL;
        $text .= 'Count of invalid rows: '.count($this->invalidRows).PHP_EOL;
        if (count($this->invalidRows) > 0) {
            $text .= PHP_EOL.'Invalid rows: '.PHP_EOL;
            foreach ($this->invalidRows as $row) {
                $text .= implode(', ', $row).PHP_EOL;
            }
        }

        return $text;
    }

    /**
     * @return string
     */
    private function getTextForInvalid(): string
    {
        $settings = Settings::fromString($this->getCsvSettings());

        $text = 'Settings of CSV:'.PHP_EOL;
        $text .= 'Delimiter: '.$settings->getDelimiter().PHP_EOL;
        $text .= 'Enclosure: '.$settings->getEnclosure().PHP_EOL;
        $text .= 'Escape: '.$settings->getEscape().PHP_EOL;
        $text .= 'Have header: '.$settings->getHaveHeader();

        return $text;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getValidRows(): ?array
    {
        return $this->validRows;
    }

    /**
     * @param string[] $validRows
     *
     * @return $this
     */
    public function setValidRows(?array $validRows): self
    {
        $this->validRows = $validRows;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getInvalidRows(): ?array
    {
        return $this->invalidRows;
    }

    /**
     * @param string[]|null $invalidRows
     *
     * @return $this
     */
    public function setInvalidRows(?array $invalidRows): self
    {
        $this->invalidRows = $invalidRows;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileOriginalName(): string
    {
        return $this->fileOriginalName;
    }

    /**
     * @param string $fileOriginalName
     *
     * @return $this
     */
    public function setFileOriginalName(string $fileOriginalName): self
    {
        $this->fileOriginalName = $fileOriginalName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileTmpName(): string
    {
        return $this->fileTmpName;
    }

    /**
     * @param string $fileTmpName
     *
     * @return $this
     */
    public function setFileTmpName(string $fileTmpName): self
    {
        $this->fileTmpName = $fileTmpName;

        return $this;
    }

    /**
     * @return string
     */
    public function getCsvSettings(): string
    {
        return $this->csvSettings;
    }

    /**
     * @param string $csvSettings
     *
     * @return $this
     */
    public function setCsvSettings(string $csvSettings): self
    {
        $this->csvSettings = $csvSettings;

        return $this;
    }

    /**
     * @return bool
     */
    public function getRemovingFile(): bool
    {
        return $this->removingFile;
    }

    /**
     * @param bool $removingFile
     *
     * @return $this
     */
    public function setRemovingFile(bool $removingFile): self
    {
        $this->removingFile = $removingFile;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }
}
