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
}
