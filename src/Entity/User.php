<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Appaydin\PdUser\Model\User as BaseUser;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: "App\Repository\UserRepository")]
#[ORM\Table(name: "user")]
#[UniqueEntity(fields: ["email"], message: "email_already_taken")]
class User extends BaseUser
{
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthdate = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $profileImage = null;

    // This property won't be persisted, just used for form handling
    private $profileImageFile;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: UserReward::class, cascade: ["persist", "remove"])]
    private Collection $userRewards;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $isFaceRecognitionEnabled = false;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $faceEmbeddings = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $facePhotoPath = null;

    // This property won't be persisted, just used for form handling
    private $facePhotoFile;

    public function __construct()
    {
        parent::__construct();
        $this->userRewards = new ArrayCollection();
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;
        return $this;
    }

    public function getProfileImage(): ?string
    {
        return $this->profileImage;
    }

    public function setProfileImage(?string $profileImage): self
    {
        $this->profileImage = $profileImage;
        return $this;
    }

    public function getProfileImageFile()
    {
        return $this->profileImageFile;
    }

    public function setProfileImageFile($profileImageFile): self
    {
        $this->profileImageFile = $profileImageFile;
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
            $userReward->setUser($this);
        }

        return $this;
    }

    public function removeUserReward(UserReward $userReward): self
    {
        if ($this->userRewards->removeElement($userReward)) {
            // set the owning side to null (unless already changed)
            if ($userReward->getUser() === $this) {
                $userReward->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Get the total points for this user
     */
    public function getTotalPoints(): int
    {
        $total = 0;
        foreach ($this->userRewards as $userReward) {
            $total += $userReward->getPoints();
        }
        return $total;
    }

    public function isFaceRecognitionEnabled(): bool
    {
        return $this->isFaceRecognitionEnabled;
    }

    public function setFaceRecognitionEnabled(bool $enabled): self
    {
        $this->isFaceRecognitionEnabled = $enabled;
        return $this;
    }

    public function getFaceEmbeddings(): ?string
    {
        return $this->faceEmbeddings;
    }

    public function setFaceEmbeddings(?string $faceEmbeddings): self
    {
        $this->faceEmbeddings = $faceEmbeddings;
        return $this;
    }

    public function getFacePhotoPath(): ?string
    {
        return $this->facePhotoPath;
    }

    public function setFacePhotoPath(?string $facePhotoPath): self
    {
        $this->facePhotoPath = $facePhotoPath;
        return $this;
    }

    public function getFacePhotoFile()
    {
        return $this->facePhotoFile;
    }

    public function setFacePhotoFile($facePhotoFile): self
    {
        $this->facePhotoFile = $facePhotoFile;
        return $this;
    }
}