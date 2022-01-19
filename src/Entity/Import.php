<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ImportRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImportRepository::class)]
class Import
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    private int $cost;

    #[ORM\Column(type: 'integer')]
    private int $count;

    #[ORM\Column(type: 'datetimetz_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $date;

    #[ORM\ManyToOne(targetEntity: product::class, inversedBy: 'imports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product;

    public function __construct(
        Product $product,
        int $cost,
        int $count,
    )
    {
        $this->setProduct($product);
        $this->setCost($cost);
        $this->setCount($count);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCost(): int
    {
        return $this->cost;
    }

    public function setCost(int $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}
