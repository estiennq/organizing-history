<?php

namespace App\Repository;

use App\Entity\HistoricCharacter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoricCharacter>
 *
 * @method HistoricCharacter|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoricCharacter|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoricCharacter[]    findAll()
 * @method HistoricCharacter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoricCharacterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoricCharacter::class);
    }

//    /**
//     * @return HistoricCharacter[] Returns an array of HistoricCharacter objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HistoricCharacter
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
