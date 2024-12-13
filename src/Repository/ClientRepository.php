<?php

namespace App\Repository;

use App\Entity\Client;
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


    
    public function filterClients(?string $telephone,?bool $createUser): array
{
    $qb = $this->createQueryBuilder('c');

    // Filtre par téléphone
    if ($telephone) {
        $qb->andWhere('c.telephone = :telephone')
           ->setParameter('telephone', $telephone);
    }
    // Vérifie si createUser est vrai ou faux
    if ($createUser !== null) {
        if ($createUser) {
            // Si createUser est vrai, on filtre les clients avec un user_id non nul
            $qb->andWhere('c.utilisateur IS NOT NULL');
        } else {
            // Si createUser est faux, on filtre les clients avec un user_id nul
            $qb->andWhere('c.utilisateur IS NULL');
        }
    }

    return $qb->getQuery()->getResult();
}

    //    /**
    //     * @return Client[] Returns an array of Client objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Client
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
