<?php

namespace App\Controller;

use App\Entity\Inode;
use App\Repository\FileHashRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class InodeController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FileHashRepository
     */
    private $fileHashRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->fileHashRepository = $this->entityManager->getRepository(Inode::class);
    }

    /**
     * @Route("/inode/broken", name="brokenvids")
     */
    public function broken()
    {
        return $this->json($this->fileHashRepository->findBroken());
    }

    /**
     * @Route("/inode/{id}", name="inode")
     */
    public function index(int $id)
    {
        $this->logger->debug('Looking up entity', [
            'class' => Inode::class,
            'id' => $id
        ]);
        $inode = $this->fileHashRepository->find($id);

        if(!$inode) {
            throw $this->createNotFoundException();
        }

        return $this->json($inode);
    }
}
