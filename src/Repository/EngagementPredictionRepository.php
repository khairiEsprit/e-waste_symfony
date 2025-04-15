<?php

namespace App\Repository;

use App\Entity\EngagementPrediction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EngagementPrediction>
 *
 * @method EngagementPrediction|null find($id, $lockMode = null, $lockVersion = null)
 * @method EngagementPrediction|null findOneBy(array $criteria, array $orderBy = null)
 * @method EngagementPrediction[]    findAll()
 * @method EngagementPrediction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EngagementPredictionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EngagementPrediction::class);
    }

    public function save(EngagementPrediction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EngagementPrediction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Get the latest prediction for a user
     */
    public function getLatestPrediction(User $user): ?EngagementPrediction
    {
        return $this->createQueryBuilder('ep')
            ->where('ep.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ep.predictedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get users with high engagement scores (for admin dashboard)
     */
    public function getHighEngagementUsers(float $threshold = 70.0, int $limit = 10): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // Use a raw SQL query to get the latest prediction for each user with high engagement
        $sql = 'SELECT ep.*, u.first_name, u.last_name, u.email, u.profile_image
                FROM engagement_prediction ep
                JOIN user u ON ep.user_id = u.id
                WHERE ep.id IN (
                    SELECT MAX(ep2.id)
                    FROM engagement_prediction ep2
                    GROUP BY ep2.user_id
                )
                AND ep.score >= :threshold
                ORDER BY ep.score DESC
                LIMIT ' . $limit;

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'threshold' => $threshold
        ]);

        return $result->fetchAllAssociative();
    }
}
