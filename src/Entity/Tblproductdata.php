<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TblproductdataRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Table(name:"tblProductData")]
#[ORM\Entity(repositoryClass: TblproductdataRepository::class)]
#[UniqueEntity(fields: ["strProductCode"])]
class Tblproductdata
{

    #[ORM\Id]
    #[ORM\Column(name: "intProductDataId", type: "integer", nullable: false, options: ["unsigned" => true])]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private int $intproductdataid;

    #[ORM\Column(name: "strProductName", type: "string", length: 50, nullable: false)]
    private string $strproductname;

    #[ORM\Column(name: "strProductDesc", type: "string", length: 255, nullable: false)]
    private string $strproductdesc;

    #[ORM\Column(name: "strProductCode", type: "string", length: 10, nullable: false, unique:true)]
    private string $strproductcode;

    #[ORM\Column(name: "dtmAdded", type: "datetime", nullable: true)]
    private ?DateTime $dtmadded;

    #[ORM\Column(name: "dtmDiscontinued", type: "datetime", nullable: true)]
    private ?DateTime $dtmdiscontinued;

    #[ORM\Column(name: "stmTimestamp", type: "datetime", nullable: false)]
    private DateTime $stmtimestamp;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Request::class, orphanRemoval: true)]
    private Collection $requests;

    public function __construct()
    {
        $this->stmtimestamp = new DateTime();
        $this->requests = new ArrayCollection();
    }

    public function getIntproductdataid(): ?int
    {
        return $this->intproductdataid;
    }

    public function getStrproductname(): ?string
    {
        return $this->strproductname;
    }

    public function setStrproductname(string $strproductname): self
    {
        $this->strproductname = $strproductname;

        return $this;
    }

    public function getStrproductdesc(): ?string
    {
        return $this->strproductdesc;
    }

    public function setStrproductdesc(string $strproductdesc): self
    {
        $this->strproductdesc = $strproductdesc;

        return $this;
    }

    public function getStrproductcode(): ?string
    {
        return $this->strproductcode;
    }

    public function setStrproductcode(string $strproductcode): self
    {
        $this->strproductcode = $strproductcode;

        return $this;
    }

    public function getDtmadded(): ?\DateTimeInterface
    {
        return $this->dtmadded;
    }

    public function setDtmadded(?\DateTimeInterface $dtmadded): self
    {
        $this->dtmadded = $dtmadded;

        return $this;
    }

    public function getDtmdiscontinued(): ?\DateTimeInterface
    {
        return $this->dtmdiscontinued;
    }

    public function setDtmdiscontinued(?\DateTimeInterface $dtmdiscontinued): self
    {
        $this->dtmdiscontinued = $dtmdiscontinued;

        return $this;
    }

    public function getStmtimestamp(): ?\DateTimeInterface
    {
        return $this->stmtimestamp;
    }

    /**
     * @return Collection|Request[]
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    public function addRequest(Request $request): self
    {
        if (!$this->requests->contains($request)) {
            $this->requests[] = $request;
            $request->setProduct($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): self
    {
        if ($this->requests->removeElement($request)) {
            // set the owning side to null (unless already changed)
            if ($request->getProduct() === $this) {
                $request->setProduct(null);
            }
        }

        return $this;
    }
}
