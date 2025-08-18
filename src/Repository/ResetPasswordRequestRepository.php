<?php

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResetPasswordRequest>
 *
 * @method ResetPasswordRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResetPasswordRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResetPasswordRequest[]    findAll()
 * @method ResetPasswordRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResetPasswordRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    public function findValidRequest(string $token): ?ResetPasswordRequest
    {
        return $this->createQueryBuilder('r')
            ->where('r.hashedToken = :token')
            ->andWhere('r.expiresAt > :now')
            ->andWhere('r.isUsed = :used')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('used', false)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findExpiredRequests(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.expiresAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }
} 