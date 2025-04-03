<?php

namespace App\Repository;

use App\Entity\Participation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    public function search(string $term): array
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.firstName LIKE :term')
            ->orWhere('p.lastName LIKE :term')
            ->orWhere('p.email LIKE :term')
            ->orWhere('p.phone LIKE :term')
            ->orWhere('p.city LIKE :term')
            ->orWhere('p.zipCode LIKE :term')
            ->orWhere('p.country LIKE :term')
            ->setParameter('term', '%'.addcslashes($term, '%_').'%')
            ->orderBy('p.id', 'ASC')
            ->getQuery();

        // Debug: Affiche la requête SQL générée
        // dump($query->getSQL()); dump($query->getParameters()); die();

        return $query->getResult();
    }
}