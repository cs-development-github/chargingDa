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

    public function findOneWithAddressByToken(string $token): ?Client
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.address', 'a')->addSelect('a')
            ->leftJoin('c.interventions', 'i')->addSelect('i')
            ->leftJoin('i.chargingStation', 's')->addSelect('s') // optionnel
            ->where('c.secureToken = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
