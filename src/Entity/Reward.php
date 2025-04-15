<?php

namespace App\Entity;

use App\Repository\RewardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RewardRepository::class)]
#[ORM\Table(name: "reward")]
class Reward
{
    public const TYPE_BADGE = 'badge';
    public const TYPE_POINTS = 'points';
    public const TYPE_OTHER = 'other';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private string $name;

    #[ORM\Column(type: "string", length: 20)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::TYPE_BADGE, self::TYPE_POINTS, self::TYPE_OTHER])]
    private string $type;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private int $pointsRequired;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank]
    private string $description;

    #[ORM\OneToMany(mappedBy: "reward", targetEntity: UserReward::class)]
    private Collection $userRewards;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $imageUrl = null;

    public function __construct()
    {
        $this->userRewards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getPointsRequired(): int
    {
        return $this->pointsRequired;
    }

    public function setPointsRequired(int $pointsRequired): self
    {
        $this->pointsRequired = $pointsRequired;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    /**
     * @return Collection<int, UserReward>
     */
    public function getUserRewards(): Collection
    {
        return $this->userRewards;
    }

    public function addUserReward(UserReward $userReward): self
    {
        if (!$this->userRewards->contains($userReward)) {
            $this->userRewards->add($userReward);
            $userReward->setReward($this);
        }

        return $this;
    }

    public function removeUserReward(UserReward $userReward): self
    {
        if ($this->userRewards->removeElement($userReward)) {
            // set the owning side to null (unless already changed)
            if ($userReward->getReward() === $this) {
                $userReward->setReward(null);
            }
        }

        return $this;
    }
}
