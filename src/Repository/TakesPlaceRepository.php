<?php

namespace App\Repository;

use App\Entity\TakesPlace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TakesPlace>
 *
 * @method TakesPlace|null find($id, $lockMode = null, $lockVersion = null)
 * @method TakesPlace|null findOneBy(array $criteria, array $orderBy = null)
 * @method TakesPlace[]    findAll()
 * @method TakesPlace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TakesPlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TakesPlace::class);
    }

//    /**
//     * @return TakesPlace[] Returns an array of TakesPlace objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TakesPlace
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
