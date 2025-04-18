<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(User $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(User $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function searchByName(string $term): array
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.firstName) LIKE LOWER(:term)')
            ->orWhere('LOWER(u.lastName) LIKE LOWER(:term)')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('u.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByPage(int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function searchByNamePaginated(string $term, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('u')
            ->where('LOWER(u.firstName) LIKE LOWER(:term)')
            ->orWhere('LOWER(u.lastName) LIKE LOWER(:term)')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('u.id', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function getTotalUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalSearchResults(string $term): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('LOWER(u.firstName) LIKE LOWER(:term)')
            ->orWhere('LOWER(u.lastName) LIKE LOWER(:term)')
            ->setParameter('term', '%' . $term . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find users by role
     *
     * @param string $role The role to search for
     * @return User[] Returns an array of User objects
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"' . $role . '%')
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
