<?php

namespace App\Repository;

use App\Entity\Intervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intervention>
 */
class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('i')
            ->orderBy('i.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findAllForGrouping(?string $borneName, ?string $clientName, ?string $referencePrefix)
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.client', 'c')
            ->leftJoin('i.installator', 'u');

        if ($borneName) {
            $qb->andWhere('i.borneName LIKE :borneName')
                ->setParameter('borneName', '%' . $borneName . '%');
        }

        if ($clientName) {
            $qb->andWhere('c.societyName LIKE :clientName')
                ->setParameter('clientName', '%' . $clientName . '%');
        }

        if ($referencePrefix) {
            $qb->andWhere('i.reference LIKE :ref')
                ->setParameter('ref', $referencePrefix . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function findGroupedByReference(?string $borneName, ?string $clientName, ?string $referencePrefix)
    {
        $qb = $this->createQueryBuilder('i')
            ->select('SUBSTRING(i.reference, 1, 24) AS refPrefix')
            ->addSelect('COUNT(i.id) AS nbBornes')
            ->addSelect('MIN(i.id) AS anyInterventionId')
            ->leftJoin('i.Client', 'c')
            ->leftJoin('i.installator', 'u');

        if ($borneName) {
            $qb->andWhere('i.borneName LIKE :borneName')
                ->setParameter('borneName', '%' . $borneName . '%');
        }

        if ($clientName) {
            $qb->andWhere('c.societyName LIKE :clientName')
                ->setParameter('clientName', '%' . $clientName . '%');
        }

        if ($referencePrefix) {
            $qb->andWhere('i.reference LIKE :ref')
                ->setParameter('ref', $referencePrefix . '%');
        }

        return $qb
            ->groupBy('SUBSTRING(i.reference, 1, 24)')
            ->orderBy('SUBSTRING(i.reference, 1, 24)', 'DESC')
            ->getQuery();
    }


    public function findByGroupPrefix(string $groupPrefix): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.reference LIKE :groupRef')
            ->setParameter('groupRef', $groupPrefix . '%')
            ->orderBy('i.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
