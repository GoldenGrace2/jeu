<?php

namespace App\Repository;

use App\Entity\Chat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Chat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chat[]    findAll()
 * @method Chat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chat::class);
    }

    public function findByChatAndPartie(Partie $partie,Chat $chat) : ?Chat
    {
        return $this->createQueryBuilder('c')
                    ->where('c.partie = :partie')
                    ->andWhere('j.joueur = :joueur')
                    ->setparameters([
                        'partie' => $partie->getId(),
                        'chat' => $chat->getId()
                    ])
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    
    public function findByChatAndUser(User $user,Chat $chat) : ?Chat
    {
        return $this->createQueryBuilder('c')
                    ->where('c.partie = :partie')
                    ->andWhere('j.joueur = :joueur')
                    ->setparameters([
                        'user' => $user->getId(),
                        'chat' => $chat->getId()
                    ])
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    // /**
    //  * @return Chat[] Returns an array of Chat objects
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
    public function findOneBySomeField($value): ?Chat
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
