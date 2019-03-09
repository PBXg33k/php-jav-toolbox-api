<?php

namespace App\Repository;

use App\Entity\JavFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JavFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method JavFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method JavFile[]    findAll()
 * @method JavFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JavFileRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JavFile::class);
    }

    public function findOneByFileInfo(\SplFileInfo $fileInfo)
    {
        return $this->findOneBy([
            'inode' => $fileInfo->getInode(),
            'path' => $fileInfo->getPathname(),
        ]);
    }

    public function findOneByPath(string $path)
    {
        return $this->findOneBy([
            'path' => $path,
        ]);
    }

    public function findOneByOrCreate(JavFile $javFile, array $lookupKeys)
    {
        $criteria = [];
        foreach ($lookupKeys as $lookupKey) {
            $criteria[$lookupKey] = $this->getClassMetadata()->getFieldValue($javFile, $lookupKey);
        }

        if ($lookup = $this->findOneBy($criteria)) {
            return $lookup;
        } else {
            return $javFile;
        }
    }

//    /**
//     * @return JavFile[] Returns an array of JavFile objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?JavFile
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
