<?php

namespace App\Repository;

use App\Entity\Poubelle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Poubelle>
 */
class PoubelleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Poubelle::class);
    }

    //    /**
    //     * @return Poubelle[] Returns an array of Poubelle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Poubelle
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function search(?string $search, ?string $etat, ?string $centre): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.centre', 'c')
            ->addSelect('c')
            ->orderBy('p.etat', 'ASC');
        
        // Vérifiez si remplissage existe, sinon utilisez id pour le tri
        if (property_exists(\App\Entity\Poubelle::class, 'remplissage')) {
            $qb->addOrderBy('p.remplissage', 'DESC');
        } else {
            $qb->addOrderBy('p.id', 'DESC');
        }

        if ($search) {
            $qb->andWhere('p.adresse LIKE :search OR c.nom LIKE :search')
               ->setParameter('search', '%'.$search.'%');
        }

        if ($etat && in_array($etat, ['Fonctionnel', 'En panne'])) {
            $qb->andWhere('p.etat = :etat')
               ->setParameter('etat', $etat);
        }

        if ($centre) {
            $qb->andWhere('c.id = :centre')
               ->setParameter('centre', $centre);
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllCentres(): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT c.id, c.nom')
            ->join('p.centre', 'c')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}