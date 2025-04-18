<?php

namespace App\Repository;

use App\Entity\GamificationAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GamificationAction>
 *
 * @method GamificationAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method GamificationAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method GamificationAction[]    findAll()
 * @method GamificationAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GamificationActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GamificationAction::class);
    }

    public function save(GamificationAction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GamificationAction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
