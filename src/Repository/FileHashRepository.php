<?php

namespace App\Repository;

use App\Entity\FileHash;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FileHash|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileHash|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileHash[]    findAll()
 * @method FileHash[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileHashRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FileHash::class);
    }

//    /**
//     * @return FileHash[] Returns an array of FileHash objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FileHash
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
