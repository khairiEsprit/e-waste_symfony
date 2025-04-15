<?php

namespace App\Entity;

use App\Repository\UserRewardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRewardRepository::class)]
#[ORM\Table(name: "user_reward")]
#[ORM\Index(columns: ["points"], name: "idx_user_reward_points")]
class UserReward
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "userRewards")]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Reward::class, inversedBy: "userRewards")]
    #[ORM\JoinColumn(nullable: true)]
    private ?Reward $reward = null;

    #[ORM\ManyToOne(targetEntity: GamificationAction::class, inversedBy: "userRewards")]
    #[ORM\JoinColumn(nullable: true)]
    private ?GamificationAction $action = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private int $points;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $earnedAt;

    public function __construct()
    {
        $this->earnedAt = new \DateTime();
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

    public function getReward(): ?Reward
    {
        return $this->reward;
    }

    public function setReward(?Reward $reward): self
    {
        $this->reward = $reward;
        return $this;
    }

    public function getAction(): ?GamificationAction
    {
        return $this->action;
    }

    public function setAction(?GamificationAction $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;
        return $this;
    }

    public function getEarnedAt(): \DateTimeInterface
    {
        return $this->earnedAt;
    }

    public function setEarnedAt(\DateTimeInterface $earnedAt): self
    {
        $this->earnedAt = $earnedAt;
        return $this;
    }
}
