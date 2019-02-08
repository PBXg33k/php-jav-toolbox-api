<?php

namespace App\Repository;

use App\Entity\Inode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Inode|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inode|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inode[]    findAll()
 * @method Inode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileHashRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Inode::class);
    }

    public function exists(int $inodeId): bool
    {
        $q = $this->createQueryBuilder('i')
            ->select('COUNT(i.id) as cnt')
            ->where('i.id = :inodeid')
            ->setParameter('inodeid', $inodeId)
            ->getQuery();
        $count = $q->execute([], Query::HYDRATE_SINGLE_SCALAR);
        $q->free();
        unset($q);
        return (bool) $count > 0;
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
