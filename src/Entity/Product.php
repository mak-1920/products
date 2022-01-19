<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'boolean', options: ['default' => 'false'])]
    private bool $discontinued;

    #[ORM\OneToMany(mappedBy: 'Product', targetEntity: Import::class, orphanRemoval: true)]
    private Collection $imports;

    public function __construct(string $name, bool $isDiscontinued = false)
    {
        $this->imports = new ArrayCollection();

        $this->setName($name);
        $this->setDiscontinued($isDiscontinued);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDiscontinued(): bool
    {
        return $this->discontinued;
    }

    public function setDiscontinued(bool $discontinued): self
    {
        $this->discontinued = $discontinued;

        return $this;
    }

    /**
     * @return Collection|Import[]
     */
    public function getImports(): Collection
    {
        return $this->imports;
    }

    public function addImport(Import $import): self
    {
        if (!$this->imports->contains($import)) {
            $this->imports[] = $import;
            $import->setProduct($this);
        }

        return $this;
    }

    public function removeImport(Import $import): self
    {
        if ($this->imports->removeElement($import)) {
            // set the owning side to null (unless already changed)
            if ($import->getProduct() === $this) {
                $import->setProduct(null);
            }
        }

        return $this;
    }
}
