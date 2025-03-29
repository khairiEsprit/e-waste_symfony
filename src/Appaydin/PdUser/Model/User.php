<?php

/**
 * This file is part of the pd-admin pd-user package.
 *
 * @package     pd-user
 * @license     LICENSE
 * @author      Ramazan APAYDIN <apaydin541@gmail.com>
 * @link        https://github.com/appaydin/pd-user
 */

namespace App\Appaydin\PdUser\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * User Account.
 *
 * @author Ramazan APAYDIN <apaydin541@gmail.com>
 */


#[ORM\Entity]
#[ORM\Table(name: "user")]
class User implements UserInterface, PasswordAuthenticatedUserInterface, \Serializable
{
    public const ROLE_DEFAULT = 'ROLE_USER';
    public const ROLE_ALL_ACCESS = 'ROLE_SUPER_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    protected int $id;

    #[ORM\Column(type: "string", length: 100)]
    protected string $password;

    #[ORM\Column(type: "string", length: 100, unique: true, nullable: true)]
    #[Assert\Email]
    protected ?string $email = null;

    #[ORM\Column(type: "boolean")]
    protected bool $active;

    #[ORM\Column(type: "boolean")]
    protected bool $freeze;

    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTime $lastLogin = null;

    #[ORM\Column(type: "string", length: 180, unique: true, nullable: true)]
    #[Assert\Length(max: 180)]
    protected ?string $confirmationToken = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTime $passwordRequestedAt = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTime $createdAt = null;

    #[ORM\Column(type: "array")]
    protected array $roles = [];

    #[ORM\ManyToMany(targetEntity: "Group")]
    #[ORM\JoinTable(
        name: "user_group_tax",
        joinColumns: [new ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "CASCADE")],
        inverseJoinColumns: [new ORM\JoinColumn(name: "group_id", referencedColumnName: "id", onDelete: "CASCADE")]
    )]
    protected iterable $groups;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(min: 2, max: 255)]
    protected ?string $firstName = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(min: 2, max: 255)]
    protected ?string $lastName = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    #[Assert\Length(max: 20)]
    protected ?string $phone = null;

    #[ORM\Column(type: "string", length: 3, nullable: true)]
    #[Assert\Language]
    protected ?string $language = null;


    public function __construct()
    {
        $this->active = true;
        $this->freeze = false;
        $this->roles = [static::ROLE_DEFAULT];
        $this->createdAt = new \DateTime();
        $this->groups = new ArrayCollection();
    }



    public function getId(): int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->email ?? '';
    }

    public function setUserIdentifier(?string $username): self
    {
        $this->email = $username;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $enabled): self
    {
        $this->active = $enabled;

        return $this;
    }

    public function isFreeze(): bool
    {
        return $this->freeze;
    }

    public function setFreeze(bool $enabled): self
    {
        $this->freeze = $enabled;

        return $this;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTime $time = null): self
    {
        $this->lastLogin = $time;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function createConfirmationToken(): self
    {
        $this->confirmationToken = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        return $this;
    }

    public function getPasswordRequestedAt(): ?\DateTime
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?\DateTime $date): self
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    public function isPasswordRequestNonExpired($ttl): bool
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime && $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $date): self
    {
        $this->createdAt = $date;

        return $this;
    }

    public function getRoles(bool $privateRoles = false): array
    {
        $roles = $this->roles ?? [];
        $groupRoles = [];

        foreach ($this->getGroups() as $group) {
            $groupRoles[] = $group->getRoles();
        }
        $groupRoles = array_merge(...$groupRoles);

        return array_unique(array_merge($roles, $groupRoles));
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRoles(array $roles): self
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    public function addRole(string $role): self
    {
        $role = mb_strtoupper($role);

        if (!\in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        if (false !== $key = array_search(mb_strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return \in_array(mb_strtoupper($role), $this->getRoles(), true);
    }

    public function getGroups(): null|PersistentCollection|ArrayCollection
    {
        return $this->groups;
    }

    public function setGroups(null|PersistentCollection|ArrayCollection $groups): self
    {
        $this->groups = $groups;

        return $this;
    }

    public function getGroupNames(): ?array
    {
        return $this->getGroups()
            ->map(function (Group $group) {
                return $group->getName();
            })
            ->toArray();
    }

    public function hasGroup(string $name): bool
    {
        return \in_array($name, $this->getGroupNames(), true);
    }

    public function addGroup(GroupInterface $group): self
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    public function removeGroup(GroupInterface $group): self
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstname): self
    {
        $this->firstName = $firstname;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastname): self
    {
        $this->lastName = $lastname;

        return $this;
    }

    public function getFullName(): ?string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // Clear any sensitive temporary data, if applicable (e.g., plain password)
        // For now, nothing to erase since no temporary credentials are stored
    }

    public function getSalt(): ?string
    {
        return null; // Not needed with modern password hashing (e.g., bcrypt)
    }

    public function getUsername(): ?string
    {
        return $this->getUserIdentifier();
    }

    public function __serialize(): array
    {
        return [
            $this->id,
            $this->password,
            $this->getUserIdentifier(),
            $this->active,
            $this->lastLogin,
            $this->createdAt,
        ];
    }

    public function __unserialize(array $data): void
    {
        [
            $this->id,
            $this->password,
            $email,
            $this->active,
            $this->lastLogin,
            $this->createdAt,
        ] = $data;

        $this->setUserIdentifier($email);
    }

    // Legacy serialize/unserialize for older PHP versions
    public function serialize(): ?string
    {
        return serialize($this->__serialize());
    }

    public function unserialize($data): void
    {
        $this->__unserialize(unserialize($data));
    }
}