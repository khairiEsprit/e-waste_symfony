<?php


namespace App\Appaydin\PdUser\Model;


interface GroupInterface
{
    public function getId(): int;
    public function getName(): ?string;
    public function setName(string $name): self;
    public function getRoles(): ?array;
    public function setRoles(array $roles): self;
    public function addRole(string $role): self;
    public function removeRole(string $role): self;
    public function hasRole(string $role): bool;
}
