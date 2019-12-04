<?php

namespace App\Repository;

use App\Entity\Inode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @method Inode|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inode|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inode[]    findAll()
 * @method Inode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileHashRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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

    public function findBroken()
    {
        return $this->findBy([
            'checked' => 1,
            'consistent' => 0,
        ]);
    }

    public function findUnchecked()
    {
        return $this->findBy([
            'checked' => 0
        ]);
    }
}
