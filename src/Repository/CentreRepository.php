<?php

namespace App\Repository;

use App\Entity\Centre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Centre>
 */
class CentreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Centre::class);
    }

    public function findOneById(int $id): ?Centre
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * Search centres by term
     *
     * @param string $term The search term
     * @return Centre[] Returns an array of Centre objects
     */
    public function searchByTerm(string $term): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.nom LIKE :term')
            ->orWhere('c.email LIKE :term')
            ->orWhere('c.telephone LIKE :term')
            ->orWhere('c.id = :exactId')
            ->setParameter('term', '%' . $term . '%')
            ->setParameter('exactId', is_numeric($term) ? $term : -1)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Filter centres by location range
     *
     * @param float $minLongitude Minimum longitude
     * @param float $maxLongitude Maximum longitude
     * @param float $minLatitude Minimum latitude
     * @param float $maxLatitude Maximum latitude
     * @return Centre[] Returns an array of Centre objects
     */
    public function filterByLocation(float $minLongitude, float $maxLongitude, float $minLatitude, float $maxLatitude): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.longitude BETWEEN :minLong AND :maxLong')
            ->andWhere('c.altitude BETWEEN :minLat AND :maxLat')
            ->setParameter('minLong', $minLongitude)
            ->setParameter('maxLong', $maxLongitude)
            ->setParameter('minLat', $minLatitude)
            ->setParameter('maxLat', $maxLatitude)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}