<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductDataRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Table(name: 'tblProductData')]
#[ORM\Entity(repositoryClass: ProductDataRepository::class)]
#[UniqueEntity(fields: ['strProductCode'])]
class ProductData
{
    #[ORM\Id]
    #[ORM\Column(name: 'intProductDataId', type: 'integer', nullable: false, options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'strProductName', type: 'string', length: 50, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'strProductDesc', type: 'string', length: 255, nullable: false)]
    private string $descriptions;

    #[ORM\Column(name: 'strProductCode', type: 'string', length: 10, unique: true, nullable: false)]
    private string $code;

    #[ORM\Column(name: 'dtmAdded', type: 'datetime', nullable: true)]
    private ?DateTime $createdAt;

    #[ORM\Column(name: 'dtmDiscontinued', type: 'datetime', nullable: true)]
    private ?DateTime $discontinuedAt;

    #[ORM\Column(name: 'stmTimestamp', type: 'datetime', nullable: false)]
    private DateTime $stmtimestamp;

    #[ORM\Column(name: 'intStock', type: 'integer')]
    private int $stock;

    #[ORM\Column(name: 'floatCost', type: 'float')]
    private float $cost;

    public function __construct()
    {
        $this->stmtimestamp = new DateTime();
        $this->createdAt = new DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescriptions(): ?string
    {
        return $this->descriptions;
    }

    /**
     * @param string $descriptions
     *
     * @return $this
     */
    public function setDescriptions(string $descriptions): self
    {
        $this->descriptions = $descriptions;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface|null $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(?DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getDiscontinuedAt(): ?DateTimeInterface
    {
        return $this->discontinuedAt;
    }

    /**
     * @param DateTimeInterface|null $discontinuedAt
     *
     * @return $this
     */
    public function setDiscontinuedAt(?DateTimeInterface $discontinuedAt): self
    {
        $this->discontinuedAt = $discontinuedAt;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getStmtimestamp(): ?DateTimeInterface
    {
        return $this->stmtimestamp;
    }

    /**
     * @return int|null
     */
    public function getStock(): ?int
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     *
     * @return $this
     */
    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getCost(): ?float
    {
        return $this->cost;
    }

    /**
     * @param float $cost
     *
     * @return $this
     */
    public function setCost(float $cost): self
    {
        $this->cost = $cost;

        return $this;
    }
}
