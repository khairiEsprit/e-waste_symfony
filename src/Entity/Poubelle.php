<?php

namespace App\Entity;

use App\Repository\PoubelleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PoubelleRepository::class)]
class Poubelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'poubelles', cascade: ['persist'])]
    #[Assert\NotBlank(message: "Le centre est obligatoire.")]
    private ?Centre $centre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire.")]
    private ?string $adresse = null;

    #[ORM\Column(nullable: false)]
    private ?int $niveau = 0;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ["Fonctionnel", "En panne"], message: "L'état doit être 'Fonctionnel' ou 'En panne'.")]
    private ?string $etat = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_installation;

    #[ORM\Column]
    #[Assert\Range(
        min: 100,
        max: 200,
        notInRangeMessage: "La hauteur totale doit être entre 100 et 200."
    )]
    private ?int $hauteur_totale = null;

    #[ORM\OneToOne(mappedBy: 'poubelle', cascade: ['persist', 'remove'])]
    private ?Capteur $capteur = null;

    #[ORM\OneToOne(mappedBy: 'poubelle', cascade: ['persist', 'remove'])]
    private ?Capteurp $capteurp = null;

    /**
     * @var Collection<int, Historique>
     */
    #[ORM\OneToMany(targetEntity: Historique::class, mappedBy: 'poubelle', orphanRemoval: true)]
    private Collection $historique;

    public function __construct()
    {
        $this->historique = new ArrayCollection();
        $this->niveau = 0;
        $this->date_installation = new \DateTime();

        // Création des capteurs avec valeurs par défaut
        $this->createDefaultCapteurs();
    }
    private function createDefaultCapteurs(): void
    {
        // Création du capteur de distance
        $this->capteur = new Capteur();
        $this->capteur->setPoubelle($this);
        $this->capteur->setDistanceMesuree(0.0);
        $this->capteur->setPorteeMaximale(200.0); // Valeur par défaut
        $this->capteur->setPrecisionCapteur(0.5);
        $this->capteur->setDateMesure(new \DateTime());

        // Création du capteur de poids (Capteurp)
        $this->capteurp = new Capteurp();
        $this->capteurp->setPoubelle($this);
        $this->capteurp->setQuantite(0.0);
        $this->capteurp->setDateM(new \DateTime());
    }

    // Getters et setters inchangés, sauf pour ceux modifiés ci-dessous


    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function setEtat(?string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function setHauteurTotale(?int $hauteur_totale): static
    {
        $this->hauteur_totale = $hauteur_totale;

        return $this;
    }

    // Les autres getters et setters restent inchangés
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCentre(): ?Centre
    {
        return $this->centre;
    }

    public function setCentre(?Centre $centre): static
    {
        $this->centre = $centre;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }
    public function getNiveau(): ?int
    {
        return $this->niveau;
    }

    public function setNiveau(?int $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }
    public function getDateInstallation(): ?\DateTimeInterface
    {
        return $this->date_installation;
    }

    public function setDateInstallation(\DateTimeInterface $date_installation): static
    {
        $this->date_installation = $date_installation;

        return $this;
    }

    public function getHauteurTotale(): ?int
    {
        return $this->hauteur_totale;
    }
    public function getCapteur(): ?Capteur
    {
        return $this->capteur;
    }

    public function setCapteur(?Capteur $capteur): static
    {
        if ($capteur !== null && $capteur->getPoubelle() !== $this) {
            $capteur->setPoubelle($this);
        }

        $this->capteur = $capteur;

        return $this;
    }

    public function getCapteurp(): ?Capteurp
    {
        return $this->capteurp;
    }

    public function setCapteurp(Capteurp $capteurp): static
    {
        // set the owning side of the relation if necessary
        if ($capteurp->getPoubelle() !== $this) {
            $capteurp->setPoubelle($this);
        }

        $this->capteurp = $capteurp;

        return $this;
    }

    /**
     * @return Collection<int, Historique>
     */
    public function getHistorique(): Collection
    {
        return $this->historique;
    }

    public function addHistorique(Historique $historique): static
    {
        if (!$this->historique->contains($historique)) {
            $this->historique->add($historique);
            $historique->setPoubelle($this);
        }

        return $this;
    }

    public function removeHistorique(Historique $historique): static
    {
        if ($this->historique->removeElement($historique)) {
            // set the owning side to null (unless already changed)
            if ($historique->getPoubelle() === $this) {
                $historique->setPoubelle(null);
            }
        }

        return $this;
    }
}