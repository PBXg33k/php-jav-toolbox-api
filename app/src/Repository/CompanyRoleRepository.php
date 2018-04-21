<?php

namespace App\Repository;

use App\Entity\CompanyRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CompanyRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyRole[]    findAll()
 * @method CompanyRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRoleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CompanyRole::class);
    }

//    /**
//     * @return CompanyRole[] Returns an array of CompanyRole objects
//     */
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
    public function findOneBySomeField($value): ?CompanyRole
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
