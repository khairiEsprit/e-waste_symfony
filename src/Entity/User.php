<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Pd\UserBundle\Model\User as BaseUser;

#[ORM\Entity(repositoryClass: "App\Repository\UserRepository")]
#[ORM\Table(name: "user")]
#[UniqueEntity(fields: ["email"], message: "email_already_taken")]
class User extends BaseUser
{
    public function __construct()
    {
        parent::__construct();
    }
}
