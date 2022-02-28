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

    /** @var string IMPORT_NEW|STATUS_FAILED|STATUS_IMPORTED $status */
    #[ORM\Column(name: 'status', type: 'string', length: 20)]
    private string $status;

    /** @var string[][] $validRows */
    #[ORM\Column(name: 'valid_rows', type: 'array')]
    private array $validRows = [];

    /** @var string[][] $invalidRows */
    #[ORM\Column(name: 'invalid_rows', type: 'array')]
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

    public function __construct()
    {
    }

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
     * @return string|false
     */
    public function toJson(): string|false
    {
        $data = [
            'id' => $this->id,
            'file' => $this->fileOriginalName,
            'status' => $this->status,
            'complete' => $this->validRows,
            'failed' => $this->invalidRows,
            'settings' => $this->csvSettings,
        ];

        $json = json_encode($data);

        return $json;
    }

    /**
     * @return string
     */
    private function getStatusHeader(): string
    {
        $statusTypeStart = (int) stripos($this->status, '_') + 1;
        $text = substr($this->status, $statusTypeStart);
        $text .= ' (id'.$this->id.')'.PHP_EOL;
        $text .= 'File: '.$this->fileOriginalName.PHP_EOL.PHP_EOL;

        return $text;
    }

    /**
     * @return string
     */
    private function getTextForValid(): string
    {
        $validRowsCount = count($this->validRows);
        $invalidRowsCount = count($this->invalidRows);

        $text = 'Requests: '.($validRowsCount + $invalidRowsCount).PHP_EOL;
        $text .= 'Count of valid rows: '.$validRowsCount.PHP_EOL;
        $text .= 'Count of invalid rows: '.$invalidRowsCount.PHP_EOL;
        if ($invalidRowsCount > 0) {
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
     * @return string[][]
     */
    public function getValidRows(): array
    {
        return $this->validRows;
    }

    /**
     * @param string[][] $validRows
     *
     * @return $this
     */
    public function setValidRows(array $validRows): self
    {
        $this->validRows = $validRows;

        return $this;
    }

    /**
     * @return string[][]
     */
    public function getInvalidRows(): array
    {
        return $this->invalidRows;
    }

    /**
     * @param string[][] $invalidRows
     *
     * @return $this
     */
    public function setInvalidRows(array $invalidRows): self
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
