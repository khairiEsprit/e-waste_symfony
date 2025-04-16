<?php

// src/Entity/Centre.php

namespace App\Entity;

use App\Repository\CentreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CentreRepository::class)]
class Centre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du centre ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le nom du centre doit comporter au moins {{ limit }} caractères.",
        maxMessage: "Le nom du centre ne peut pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z\s]+$/",
        message: "Le nom du centre ne peut contenir que des lettres et des espaces, pas de chiffres."
    )]
    private ?string $nom = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La longitude ne peut pas être vide.")]
    #[Assert\Range(
        min: -180,
        max: 180,
        notInRangeMessage: "La longitude doit être comprise entre {{ min }} et {{ max }}."
    )]
    private ?float $longitude = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "L'altitude ne peut pas être vide.")]
    #[Assert\Range(
        min: -10000,
        max: 10000,
        notInRangeMessage: "L'altitude doit être comprise entre {{ min }} et {{ max }}."
    )]
    private ?float $altitude = null;

    #[ORM\Column(length: 8)]
    #[Assert\NotBlank(message: "Le numéro de téléphone ne peut pas être vide.")]
    #[Assert\Length(
        exactMessage: "Le numéro de téléphone doit comporter exactement 8 chiffres.",
        min: 8,
        max: 8
    )]
    #[Assert\Regex(
        pattern: "/^\d{8}$/",
        message: "Le numéro de téléphone doit être composé exactement de 8 chiffres."
    )]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse email ne peut pas être vide.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;

    
        /**
     * @var Collection<int, Poubelle>
     */
    #[ORM\OneToMany(targetEntity: Poubelle::class, mappedBy: 'centre')]
    private Collection $poubelles;

    #[ORM\OneToMany(targetEntity: Demande::class, mappedBy: 'centre')]
    private Collection $demandes;

    public function __construct()
    {
        $this->poubelles = new ArrayCollection();
        $this->demandes = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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


    public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(Demande $demande): static
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes->add($demande);
            $demande->setCentre($this);
        }

        return $this;
    }

    public function removeDemande(Demande $demande): static
    {
        if ($this->demandes->removeElement($demande)) {
            // set the owning side to null (unless already changed)
            if ($demande->getCentre() === $this) {
                $demande->setCentre(null);
            }
        }

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Poubelle>
     */
    public function getPoubelles(): Collection
    {
        return $this->poubelles;
    }

    public function addPoubelle(Poubelle $poubelle): static
    {
        if (!$this->poubelles->contains($poubelle)) {
            $this->poubelles->add($poubelle);
            $poubelle->setCentre($this);
        }

        return $this;
    }

    public function removePoubelle(Poubelle $poubelle): static
    {
        if ($this->poubelles->removeElement($poubelle)) {
            // set the owning side to null (unless already changed)
            if ($poubelle->getCentre() === $this) {
                $poubelle->setCentre(null);
            }
        }

        return $this;
    }
}