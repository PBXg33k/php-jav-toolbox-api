<?php

namespace App\Repository;

use App\Entity\ModelAlias;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ModelAlias|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModelAlias|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModelAlias[]    findAll()
 * @method ModelAlias[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModelAliasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModelAlias::class);
    }

//    /**
//     * @return ModelAlias[] Returns an array of ModelAlias objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ModelAlias
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
