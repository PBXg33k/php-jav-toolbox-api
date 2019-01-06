<?php

namespace App\Controller;

use App\Entity\JavFile;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use SplFileInfo;

class ThumbnailController extends AbstractController
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $thumbDir;

    /**
     * ThumbnailController constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $javToolboxMediaThumbDirectory
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        string $javToolboxMediaThumbDirectory
    ) {
        $this->entityManager = $entityManager;
        $this->logger        = $logger;
        $this->thumbDir      = $javToolboxMediaThumbDirectory;
    }

    /**
     * @Route("/thumbnail/{fileId}", name="thumbnail_by_id", requirements={"fileId"="\d+"})
     */
    public function getThumbnailByFileId(Request $request, int $fileId)
    {
        try {
            /** @var JavFile $javFile */
            $javFile = $this->entityManager->getRepository(JavFile::class)
                ->find($fileId);

            $pathInfo = pathinfo($javFile->getPath());

            if(!$javFile) {
                throw new EntityNotFoundException('File not found');
            }

            $imagePath = "{$this->thumbDir}{$pathInfo['dirname']}/{$pathInfo['filename']}.jpg";
            $imageInfo = new SplFileInfo($imagePath);

            if (!$imageInfo->isReadable()) {
                throw new \Exception('path is not readable');
            }

            if(!$imageInfo->isFile()) {
                throw new \Exception('path is not a file');
            }

            $response =  new BinaryFileResponse($imageInfo);
            $response->setAutoEtag();

            return $response;

        } catch (EntityNotFoundException $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw $this->createNotFoundException($exception->getMessage());
        }
    }
}
