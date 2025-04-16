<?php

namespace App\Entity;

use App\Repository\HistoriqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HistoriqueRepository::class)]
class Historique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "La date de l'événement est obligatoire")]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $date_evenement = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type d'événement est obligatoire")]
    #[Assert\Choice(choices: ["Collecte", "Traitement", "Recyclage", "Maintenance"], message: "Choisissez un type d'événement valide")]
    private ?string $type_evenement = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "La description ne peut pas dépasser {{ limit }} caractères")]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La quantité de déchets est obligatoire")]
    #[Assert\Positive(message: "La quantité doit être un nombre positif")]
    #[Assert\LessThan(value: 1000, message: "La quantité maximale est de 1000 kg")]
    private ?float $quantite_dechets = null;

    #[ORM\ManyToOne(inversedBy: 'historique')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Sélectionnez une poubelle")]
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
