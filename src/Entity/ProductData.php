<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductDataRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
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
    private int $intproductdataid;

    #[ORM\Column(name: 'strProductName', type: 'string', length: 50, nullable: false)]
    private string $strproductname;

    #[ORM\Column(name: 'strProductDesc', type: 'string', length: 255, nullable: false)]
    private string $strproductdesc;

    #[ORM\Column(name: 'strProductCode', type: 'string', length: 10, unique: true, nullable: false)]
    private string $strproductcode;

    #[ORM\Column(name: 'dtmAdded', type: 'datetime', nullable: true)]
    private ?DateTime $dtmadded;

    #[ORM\Column(name: 'dtmDiscontinued', type: 'datetime', nullable: true)]
    private ?DateTime $dtmdiscontinued;

    #[ORM\Column(name: 'stmTimestamp', type: 'datetime', nullable: false)]
    private DateTime $stmtimestamp;

    #[ORM\Column(type: 'float')]
    private int $stock;

    #[ORM\Column(type: 'float')]
    private $cost;

    public function __construct()
    {
        $this->stmtimestamp = new DateTime();
        $this->requests = new ArrayCollection();
        $this->dtmadded = new DateTime();
    }

    /**
     * @return int|null
     */
    public function getIntproductdataid(): ?int
    {
        return $this->intproductdataid;
    }

    /**
     * @return string|null
     */
    public function getStrproductname(): ?string
    {
        return $this->strproductname;
    }

    /**
     * @param string $strproductname
     *
     * @return $this
     */
    public function setStrproductname(string $strproductname): self
    {
        $this->strproductname = $strproductname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStrproductdesc(): ?string
    {
        return $this->strproductdesc;
    }

    /**
     * @param string $strproductdesc
     *
     * @return $this
     */
    public function setStrproductdesc(string $strproductdesc): self
    {
        $this->strproductdesc = $strproductdesc;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStrproductcode(): ?string
    {
        return $this->strproductcode;
    }

    /**
     * @param string $strproductcode
     *
     * @return $this
     */
    public function setStrproductcode(string $strproductcode): self
    {
        $this->strproductcode = $strproductcode;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getDtmadded(): ?DateTimeInterface
    {
        return $this->dtmadded;
    }

    /**
     * @param DateTimeInterface|null $dtmadded
     *
     * @return $this
     */
    public function setDtmadded(?DateTimeInterface $dtmadded): self
    {
        $this->dtmadded = $dtmadded;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getDtmdiscontinued(): ?DateTimeInterface
    {
        return $this->dtmdiscontinued;
    }

    /**
     * @param DateTimeInterface|null $dtmdiscontinued
     *
     * @return $this
     */
    public function setDtmdiscontinued(?DateTimeInterface $dtmdiscontinued): self
    {
        $this->dtmdiscontinued = $dtmdiscontinued;

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
