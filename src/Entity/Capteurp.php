<?php

namespace App\Entity;

use App\Repository\CapteurpRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CapteurpRepository::class)]
class Capteurp
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_m = null;

    #[ORM\OneToOne(inversedBy: 'capteurp', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Poubelle $poubelle = null;
    public function __construct()
    {
        $this->quantite = 0.0;
        $this->date_m = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getDateM(): ?\DateTimeInterface
    {
        return $this->date_m;
    }

    public function setDateM(\DateTimeInterface $date_m): static
    {
        $this->date_m = $date_m;

        return $this;
    }

    public function getPoubelle(): ?Poubelle
    {
        return $this->poubelle;
    }

    public function setPoubelle(Poubelle $poubelle): static
    {
        $this->poubelle = $poubelle;

        return $this;
    }
}
