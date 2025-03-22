<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Pd\UserBundle\Model\Group as BaseGroup;

#[ORM\Entity(repositoryClass: "App\Repository\GroupRepository")]
#[ORM\Table(name: "user_group")]
class Group extends BaseGroup
{
}