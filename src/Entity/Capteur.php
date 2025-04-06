<?php

namespace App\Entity;

use App\Repository\CapteurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CapteurRepository::class)]
class Capteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_c=null;

    #[ORM\Column(type: 'float')]
    private ?float $distance_mesuree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true ,options:["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $date_mesure = null;

    #[ORM\Column(type:'float')]
    private ?float $porteeMaximale = 0.0;

    #[ORM\Column (type: 'float')]
    private ?float $precision_capteur = 0.5;

    #[ORM\OneToOne(inversedBy: 'capteur', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Poubelle $poubelle = null;

    public function getId(): ?int
    {
        return $this->id_c;
    }

    public function getDistanceMesuree(): ?float
    {
        return $this->distance_mesuree;
    }

    public function setDistanceMesuree(float $distance_mesuree): static
    {
        $this->distance_mesuree = $distance_mesuree;

        return $this;
    }

    public function getDateMesure(): ?\DateTimeInterface
    {
        return $this->date_mesure;
    }

    public function setDateMesure(?\DateTimeInterface $date_mesure): static
    {
        $this->date_mesure = $date_mesure;

        return $this;
    }

    public function getPorteeMaximale(): ?float
    {
        return $this->porteeMaximale;
    }

    public function setPorteeMaximale(float $porteeMaximale): static
    {
        $this->porteeMaximale = $porteeMaximale;

        return $this;
    }

    public function getPrecisionCapteur(): ?float
    {
        return $this->precision_capteur;
    }

    public function setPrecisionCapteur(float $precision_capteur): static
    {
        $this->precision_capteur = $precision_capteur;

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
