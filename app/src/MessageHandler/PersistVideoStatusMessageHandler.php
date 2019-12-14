<?php


namespace App\MessageHandler;


use App\Entity\Inode;
use App\Entity\JavFile;
use Doctrine\ORM\EntityManagerInterface;
use Pbxg33k\MessagePack\Message\PersistVideoStatusMessage;

class PersistVideoStatusMessageHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(PersistVideoStatusMessage $message)
    {
        $javFileRepository = $this->entityManager->getRepository(JavFile::class);

        /** @var JavFile $file */
        $file = $javFileRepository->findOneByPath($message->getPath());

        if(!$file) {
            $fileinfo = new \SplFileInfo($message->getPath());

            $inode = (new Inode())
                ->setId($fileinfo->getInode())
                ->setFilesize($fileinfo->getSize())
                ->setProcessed($message->isChecked())
                ->setConsistent($message->isConsistent());

            $file = (new JavFile())
                ->setPath($message->getPath())
                ->setFilename($fileinfo->getFilename())
                ->setInode($inode);

            $this->entityManager->persist($inode);
            $this->entityManager->persist($file);
        } else {
            $inode = $file->getInode();
            $inode->setConsistent($message->isConsistent())
                ->setChecked($message->isChecked());
            $this->entityManager->persist($inode);
        }
        $this->entityManager->flush();
    }
}
