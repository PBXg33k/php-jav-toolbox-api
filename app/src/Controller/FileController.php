<?php

namespace App\Controller;

use App\Entity\JavFile;
use App\Repository\JavFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends AbstractController
{
    /**
     * @var JavFileRepository
     */
    private $fileRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    )
    {
        $this->fileRepository = $entityManager->getRepository(JavFile::class);
        $this->logger = $logger;

    }

    /**
     * @Route(
     *     "/file/{id}",
     *     condition="context.getMethod() in ['GET', 'HEAD']",
     *     name="file_by_id",
     *     requirements={"id"="\d+"}
     * )
     */
    public function fileById(int $id)
    {
        if($file = $this->fileRepository->find($id)) {
            return $this->constructResponse($file);
        }

        throw new NotFoundHttpException();
    }

    /**
     * @Route(
     *     "/file/{path}",
     *     condition="context.getMethod() in ['GET', 'HEAD']",
     *     name="file_by_path",
     *     requirements={"path"="[\/\w\-. ]+"}
     * )
     */
    public function fileByPath(string $path)
    {
        if($file = $this->fileRepository->findOneByPath($path)) {
            return $this->constructResponse($file);
        }

        if ( $file = $this->fileRepository->findOneByPath('/'.$path)) {
            return $this->constructResponse($file);
        }

        throw new NotFoundHttpException();
    }

    private function constructResponse(JavFile $javFile)
    {
        if(is_file($javFile->getPath())) {
            return new JsonResponse($javFile);
        }

        throw new NotFoundHttpException('File not found');
    }
}
