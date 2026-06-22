<?php

namespace App\Repository;

use App\Entity\CharacterPosition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CharacterPosition>
 *
 * @method CharacterPosition|null find($id, $lockMode = null, $lockVersion = null)
 * @method CharacterPosition|null findOneBy(array $criteria, array $orderBy = null)
 * @method CharacterPosition[]    findAll()
 * @method CharacterPosition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CharacterPositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CharacterPosition::class);
    }

//    /**
//     * @return CharacterPosition[] Returns an array of CharacterPosition objects
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

//    public function findOneBySomeField($value): ?CharacterPosition
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
