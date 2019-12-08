<?php

namespace App\Repository;

use App\Entity\Title;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Title|null find($id, $lockMode = null, $lockVersion = null)
 * @method Title|null findOneBy(array $criteria, array $orderBy = null)
 * @method Title[]    findAll()
 * @method Title[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TitleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
}
