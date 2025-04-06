<?php

namespace App\Entity;

use App\Repository\CentreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CentreRepository::class)]
class Centre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column (type: 'integer')]
    private  $id ;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 20)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    /**
     * @var Collection<int, Poubelle>
     */
    #[ORM\OneToMany(targetEntity: Poubelle::class, mappedBy: 'centre')]
    private Collection $poubelles;

    public function __construct()
    {
        $this->poubelles = new ArrayCollection();
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

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

    public function setEmail(?string $email): static
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
