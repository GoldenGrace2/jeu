<?php

namespace App\Repository;

use App\Entity\Cartes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Cartes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cartes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cartes[]    findAll()
 * @method Cartes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cartes::class);
    }

    // /**
    //  * @return Cartes[] Returns an array of Cartes objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Cartes
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
