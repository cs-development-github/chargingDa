<?php

namespace App\Repository;

use App\Entity\Intervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    /**
     * Trouver la prochaine intervention prévue pour un installateur (utilisateur)
     */
    public function findNextInterventionByUser($user): ?Intervention
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.installer = :user') // Correction ici
            ->setParameter('user', $user)
            ->setMaxResults(1) // Prendre uniquement la prochaine
            ->getQuery()
            ->getOneOrNullResult();
    }
}