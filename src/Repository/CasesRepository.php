<?php

namespace App\Repository;

use App\Entity\Cases;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Cases|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cases|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cases[]    findAll()
 * @method Cases[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CasesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cases::class);
    }

    public function getDataCase(?int $position): ?Cases
    {
        //cette méthode récupère les infos de la case sur laquelle se trouve le joueur
        //et retourne un tableau pour le JSON
        return $this->createQueryBuilder('c')
            ->where('c.position = :position')
            ->setParameter('position', $position)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
