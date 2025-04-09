<?php

namespace App\Entity;

use App\Repository\TacheRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TacheRepository::class)]
class Tache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le centre est requis")]
    #[Assert\Positive(message: "L'ID du centre doit être positif")]
    private ?int $id_centre = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "L'employé est requis")]
    #[Assert\Positive(message: "L'ID de l'employé doit être positif")]
    private ?int $id_employe = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: "L'altitude est requise")]
    #[Assert\Range(
        min: -1000,
        max: 10000,
        notInRangeMessage: "L'altitude doit être entre {{ min }} et {{ max }} mètres"
    )]
    private ?float $altitude = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: "La longitude est requise")]
    #[Assert\Range(
        min: -180,
        max: 180,
        notInRangeMessage: "La longitude doit être entre {{ min }} et {{ max }} degrés"
    )]
    private ?float $longitude = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le message est requis")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le message ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $message = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'état est requis")]
    #[Assert\Length(
        max: 255,
        maxMessage: "L'état ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $etat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCentre(): ?int
    {
        return $this->id_centre;
    }

    public function setIdCentre(int $id_centre): static
    {
        $this->id_centre = $id_centre;
        return $this;
    }

    public function getIdEmploye(): ?int
    {
        return $this->id_employe;
    }

    public function setIdEmploye(int $id_employe): static
    {
        $this->id_employe = $id_employe;
        return $this;
    }

    public function getAltitude(): ?float
    {
        return $this->altitude;
    }

    public function setAltitude(float $altitude): static
    {
        $this->altitude = $altitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;
        return $this;
    }
}