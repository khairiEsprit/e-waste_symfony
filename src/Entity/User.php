<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Appaydin\PdUser\Model\User as BaseUser;
use Doctrine\DBAL\Types\Types;

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

    public function __construct()
    {
        parent::__construct();
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
}