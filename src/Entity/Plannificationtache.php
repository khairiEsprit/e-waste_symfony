<?php

namespace App\Entity;

use App\Repository\PlannificationtacheRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlannificationtacheRepository::class)]
class Plannificationtache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La priorité est requise")]
    #[Assert\Length(
        max: 255,
        maxMessage: "La priorité ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Choice(
        choices: ['haute', 'moyenne', 'basse'],
        message: "La priorité doit être 'haute', 'moyenne' ou 'basse'"
    )]
    private ?string $priorite = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date limite est requise")]
    #[Assert\GreaterThanOrEqual(
        value: "today",
        message: "La date limite doit être aujourd'hui ou une date future"
    )]
    private ?\DateTimeInterface $date_limite = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: "id_tache", referencedColumnName: "id", nullable: false)]
    #[Assert\NotNull(message: "La tâche est requise")]
    private ?Tache $id_tache = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPriorite(): ?string
    {
        return $this->priorite;
    }

    public function setPriorite(string $priorite): static
    {
        $this->priorite = $priorite;
        return $this;
    }

    public function getDateLimite(): ?\DateTimeInterface
    {
        return $this->date_limite;
    }

    public function setDateLimite(\DateTimeInterface $date_limite): static
    {
        $this->date_limite = $date_limite;
        return $this;
    }

    public function getIdTache(): ?Tache
    {
        return $this->id_tache;
    }

    public function setIdTache(?Tache $id_tache): static
    {
        $this->id_tache = $id_tache;
        return $this;
    }
}