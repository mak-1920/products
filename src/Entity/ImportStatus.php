<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ImportStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImportStatusRepository::class)]
class ImportStatus
{
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
}
