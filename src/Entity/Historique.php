<?php

namespace App\Entity;

use App\Repository\HistoriqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueRepository::class)]
class Historique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_evenement = null;

    #[ORM\Column(length: 255)]
    private ?string $type_evenement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $quantite_dechets = null;

    #[ORM\ManyToOne(inversedBy: 'historique')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Poubelle $poubelle = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateEvenement(): ?\DateTimeInterface
    {
        return $this->date_evenement;
    }

    public function setDateEvenement(\DateTimeInterface $date_evenement): static
    {
        $this->date_evenement = $date_evenement;

        return $this;
    }

    public function getTypeEvenement(): ?string
    {
        return $this->type_evenement;
    }

    public function setTypeEvenement(string $type_evenement): static
    {
        $this->type_evenement = $type_evenement;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getQuantiteDechets(): ?float
    {
        return $this->quantite_dechets;
    }

    public function setQuantiteDechets(float $quantite_dechets): static
    {
        $this->quantite_dechets = $quantite_dechets;

        return $this;
    }

    public function getPoubelle(): ?Poubelle
    {
        return $this->poubelle;
    }

    public function setPoubelle(?Poubelle $poubelle): static
    {
        $this->poubelle = $poubelle;

        return $this;
    }
}
