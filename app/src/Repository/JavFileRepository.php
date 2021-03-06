<?php

namespace App\Repository;

use App\Entity\JavFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method JavFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method JavFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method JavFile[]    findAll()
 * @method JavFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JavFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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

    public function findUnchecked()
    {
        return $this->createQueryBuilder('f')
            ->join('f.inode', 'i', 'WITH', 'i.checked = 0')
            ->getQuery()
            ->execute();
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
}
