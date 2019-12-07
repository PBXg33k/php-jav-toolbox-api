<?php


namespace App\MessageHandler;


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

        $inode = $file->getInode();
        $inode->setConsistent($message->isConsistent())
              ->setChecked($message->isChecked());

        $this->entityManager->persist($inode);
        $this->entityManager->flush();
    }
}
