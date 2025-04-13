<?php

namespace App\Entity;

use App\Repository\DemandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DemandeRepository::class)]
class Demande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "L'adresse ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide.")]
    #[Assert\Email(message: "L'email {{ value }} n'est pas valide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "L'email ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $email_utilisateur = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le message ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le message ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $message = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type ne peut pas être vide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le type ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $type = null;


    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(name: "utilisateur_id", referencedColumnName: "id", nullable: true)]
    private ?User $utilisateur = null;

    /**
     * @var Collection<int, Traitement>
     */
    #[ORM\OneToMany(mappedBy: 'demande', targetEntity: Traitement::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $traitements;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(name: "center_id", referencedColumnName: "id", nullable: true)]
    private ?Center $center = null;

    public function __construct()
    {
        $this->traitements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getEmailUtilisateur(): ?string
    {
        return $this->email_utilisateur;
    }

    public function setEmailUtilisateur(string $email_utilisateur): static
    {
        $this->email_utilisateur = $email_utilisateur;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * @return Collection<int, Traitement>
     */
    public function getTraitements(): Collection
    {
        return $this->traitements;
    }

    public function addTraitement(Traitement $traitement): static
    {
        if (!$this->traitements->contains($traitement)) {
            $this->traitements->add($traitement);
            $traitement->setDemande($this);
        }

        return $this;
    }

    public function removeTraitement(Traitement $traitement): static
    {
        if ($this->traitements->removeElement($traitement)) {
            if ($traitement->getDemande() === $this) {
                $traitement->setDemande(null);
            }
        }

        return $this;
    }

    public function getCenter(): ?Center
    {
        return $this->center;
    }

    public function setCenter(?Center $center): static
    {
        $this->center = $center;

        return $this;
    }
}
