<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserReward;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserReward>
 *
 * @method UserReward|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserReward|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserReward[]    findAll()
 * @method UserReward[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRewardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserReward::class);
    }

    public function save(UserReward $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserReward $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Get leaderboard data (top users by points)
     */
    public function getLeaderboard(int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('ur')
            ->select('u.id, u.firstName, u.lastName, u.email, u.profileImage, SUM(ur.points) as totalPoints')
            ->join('ur.user', 'u')
            ->groupBy('u.id')
            ->orderBy('totalPoints', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get user's rank in the leaderboard
     */
    public function getUserRank(User $user): ?int
    {
        $conn = $this->getEntityManager()->getConnection();

        // First get all users ordered by points
        $sql = 'SELECT user_id, SUM(points) as total_points
                FROM user_reward
                GROUP BY user_id
                ORDER BY total_points DESC';

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        $rankings = $result->fetchAllAssociative();

        // Find the user's position in the rankings
        $rank = null;
        foreach ($rankings as $index => $row) {
            if ((int) $row['user_id'] === $user->getId()) {
                $rank = $index + 1; // +1 because array is 0-indexed
                break;
            }
        }

        return $rank;
    }

    /**
     * Get user's action history
     */
    public function getUserActionHistory(User $user, int $limit = 10): array
    {
        $results = $this->createQueryBuilder('ur')
            ->select('ur, a.name as actionName')
            ->leftJoin('ur.action', 'a')
            ->where('ur.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ur.earnedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // Transform the results to a more consistent format
        $history = [];
        foreach ($results as $result) {
            $userReward = $result[0];
            $history[] = [
                'earnedAt' => $userReward->getEarnedAt(),
                'actionName' => $result['actionName'] ?? 'Bonus Points',
                'points' => $userReward->getPoints()
            ];
        }

        return $history;
    }

    /**
     * Get user's total points
     */
    public function getUserTotalPoints(User $user): int
    {
        $result = $this->createQueryBuilder('ur')
            ->select('SUM(ur.points) as totalPoints')
            ->where('ur.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * Get user's action count
     */
    public function getUserActionCount(User $user): int
    {
        $result = $this->createQueryBuilder('ur')
            ->select('COUNT(ur.id) as actionCount')
            ->where('ur.user = :user')
            ->andWhere('ur.action IS NOT NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * Get points earned in the last 30 days (for charts)
     */
    public function getPointsLast30Days(User $user = null): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT DATE(earned_at) as date, SUM(points) as points
               FROM user_reward
               WHERE earned_at >= :date';

        $params = ['date' => (new \DateTime('-30 days'))->format('Y-m-d')];

        if ($user) {
            $sql .= ' AND user_id = :user_id';
            $params['user_id'] = $user->getId();
        }

        $sql .= ' GROUP BY DATE(earned_at) ORDER BY date ASC';

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery($params);

        return $result->fetchAllAssociative();
    }
}
