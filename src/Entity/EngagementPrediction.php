<?php

namespace App\Entity;

use App\Repository\EngagementPredictionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EngagementPredictionRepository::class)]
#[ORM\Table(name: "engagement_prediction")]
class EngagementPrediction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 100)]
    private float $score;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $predictedAt;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $aiResponse = null;

    public function __construct()
    {
        $this->predictedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function setScore(float $score): self
    {
        $this->score = $score;
        return $this;
    }

    public function getPredictedAt(): \DateTimeInterface
    {
        return $this->predictedAt;
    }

    public function setPredictedAt(\DateTimeInterface $predictedAt): self
    {
        $this->predictedAt = $predictedAt;
        return $this;
    }

    public function getAiResponse(): ?string
    {
        return $this->aiResponse;
    }

    public function setAiResponse(?string $aiResponse): self
    {
        $this->aiResponse = $aiResponse;
        return $this;
    }
}
