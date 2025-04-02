<?php


namespace App\Appaydin\PdUser\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
#[ORM\Table(name: "user_group")]
class Group implements GroupInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    protected ?int $id = null;

    #[ORM\Column(type: "string", unique: true, length: 180)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 180)]
    protected ?string $name = null;

    #[ORM\Column(type: "array")]
    protected ?array $roles = [];
    
    public function __construct($name, $roles = [])
    {
        $this->name = $name;
        $this->roles = $roles;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): GroupInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): GroupInterface
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): GroupInterface
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = mb_strtoupper($role);
        }

        return $this;
    }

    public function removeRole(string $role): GroupInterface
    {
        if (false !== $key = array_search(mb_strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return \in_array(mb_strtoupper($role), $this->roles, true);
    }
}
