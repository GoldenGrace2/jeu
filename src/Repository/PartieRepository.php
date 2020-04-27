<?php

namespace App\Repository;

use App\Entity\Jouer;
use App\Entity\Partie;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Partie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Partie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Partie[]    findAll()
 * @method Partie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partie::class);
    }

    public function findMyParties(User $user)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin(Jouer::class, 'j', 'WITH', 'p.id=j.partie')
            ->where('j.joueur = :joueur')
            ->setParameter('joueur', $user->getId())
            ->orderBy('p.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

  
}
