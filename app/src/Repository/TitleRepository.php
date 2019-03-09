<?php

namespace App\Repository;

use App\Entity\Title;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Title|null find($id, $lockMode = null, $lockVersion = null)
 * @method Title|null findOneBy(array $criteria, array $orderBy = null)
 * @method Title[]    findAll()
 * @method Title[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TitleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Title::class);
    }

    public function findWithBrokenFiles()
    {
        return $this->createQueryBuilder('title')
            ->where('inode.checked = 1')
            ->andWhere('inode.consistent = false')
            ->innerJoin('title.files', 'files')
            ->innerJoin('files.inode', 'inode')
            ->getQuery()
            ->getResult();
    }

//    public function getWithBrokenFiles()
//    {
//        $qb = $this->createQueryBuilder('jav_file')
//            ->andWhere('jav_file')
//    }

//    /**
//     * @return Title[] Returns an array of Title objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Title
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
