<?php

namespace App\Repository;

use App\Entity\FaceLoginAttempt;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FaceLoginAttempt>
 */
class FaceLoginAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaceLoginAttempt::class);
    }

    /**
     * Count recent failed attempts for a user
     */
    public function countRecentFailedAttempts(User $user, int $minutes = 30): int
    {
        $timeAgo = new \DateTime();
        $timeAgo->modify("-{$minutes} minutes");

        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.user = :user')
            ->andWhere('f.success = :success')
            ->andWhere('f.timestamp >= :timeAgo')
            ->setParameter('user', $user)
            ->setParameter('success', false)
            ->setParameter('timeAgo', $timeAgo)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get recent login attempts for a user
     */
    public function findRecentAttemptsByUser(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->orderBy('f.timestamp', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get recent failed attempts from an IP address
     */
    public function countRecentFailedAttemptsFromIp(string $ipAddress, int $minutes = 30): int
    {
        $timeAgo = new \DateTime();
        $timeAgo->modify("-{$minutes} minutes");

        return $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.ipAddress = :ipAddress')
            ->andWhere('f.success = :success')
            ->andWhere('f.timestamp >= :timeAgo')
            ->setParameter('ipAddress', $ipAddress)
            ->setParameter('success', false)
            ->setParameter('timeAgo', $timeAgo)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
