<?php

namespace App\Repository;

use App\Entity\CompanyRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CompanyRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyRole[]    findAll()
 * @method CompanyRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyRole::class);
    }
}
