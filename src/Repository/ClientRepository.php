<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function findByUserOrTeam(User $user)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.createdBy', 'creator')
            ->leftJoin('creator.team', 'team')
            ->where('creator = :user OR team = :team')
            ->setParameter('user', $user)
            ->setParameter('team', $user->getTeam()) // Supposons que User a une relation vers son équipe
            ->getQuery()
            ->getResult();
    }
}
