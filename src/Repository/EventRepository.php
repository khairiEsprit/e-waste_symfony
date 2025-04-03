<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Event[] Returns an array of upcoming Event objects
     */
    public function findUpcomingEvents(int $limit = null): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.date > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.date', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Event[] Returns an array of past Event objects
     */
    public function findPastEvents(int $limit = null): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.date < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Event[] Returns events with available places
     */
    public function findAvailableEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.remainingPlaces > 0')
            ->andWhere('e.date > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countAllEvents(): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function createOrderedByDateQueryBuilder(string $filter = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->orderBy('e.date', 'ASC');

        if ($filter === 'upcoming') {
            $queryBuilder
                ->where('e.date > :now')
                ->setParameter('now', new \DateTime());
        } elseif ($filter === 'past') {
            $queryBuilder
                ->where('e.date < :now')
                ->setParameter('now', new \DateTime());
        }

        return $queryBuilder;
    }
}