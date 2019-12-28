<?php

namespace App\Controller;

use App\Entity\JavFile;
use App\Repository\JavFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DownloadController extends AbstractController
{
    /**
     * @var JavFileRepository
     */
    private $fileRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $XSendFileRoot;

    private $javToolboxMediaFileLocation;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        string $javToolboxMediaFileLocation,
        string $XSendFileRoot
    )
    {
        $this->fileRepository = $entityManager->getRepository(JavFile::class);
        $this->logger = $logger;

        $this->javToolboxMediaFileLocation = $javToolboxMediaFileLocation;
        $this->XSendFileRoot = $XSendFileRoot;
    }

    /**
     * @Route(
     *     "/download/{id}",
     *     condition="context.getMethod() in ['GET', 'HEAD']",
     *     name="download_id",
     *     requirements={"id"="\d+"}
     * )
     */
    public function downloadUsingId(int $id)
    {
        if($file = $this->fileRepository->find($id)) {
            return $this->serveDownloadUsingXSendfile($file);
        }

        throw new NotFoundHttpException();
    }

    /**
     * @Route(
     *     "/download/{path}",
     *     condition="context.getMethod() in ['GET', 'HEAD']",
     *     name="download_path",
     *     requirements={"path"="[\/\w\-. ]+"}
     * )
     */
    public function downloadUsingPath(string $path)
    {
        $this->logger->info('download by file', [
            'path' => $path
        ]);

        if($file = $this->fileRepository->findOneByPath($path)) {
            return $this->serveDownloadUsingXSendfile($file);
        } elseif ( $file = $this->fileRepository->findOneByPath('/'.$path)) {
            return $this->serveDownloadUsingXSendfile($file);
        }

        throw new NotFoundHttpException();
    }

    /**
     * Creates a redirect response which is picked up by nginx as a static file.
     * This results in nginx serving out the download, freeing up resources for PHP
     *
     * @param JavFile $javFile
     * @return Response
     *
     * @todo replace filename with the title id once that has been sorted out
     */
    private function serveDownloadUsingXSendfile(JavFile $javFile)
    {

        $path = str_replace(
            $this->javToolboxMediaFileLocation,
            $this->XSendFileRoot,
            $javFile->getPath()
        );

        $this->logger->info('Serve download',[
            'javid' => $javFile->getId(),
            'inodeid' => $javFile->getInode()->getId(),
            'path' => $javFile->getPath(),
            'nginxroute' => $path
        ]);

        return new Response('Download started',200,[
            'X-Accel-Redirect'=> $path,
            'Content-Type' => mime_content_type($javFile->getPath()),
            'Content-Disposition' => sprintf("attachment; filename=%s", $javFile->getFilename()),
            'Content-Length' => $javFile->getInode()->getFilesize()
        ]);

    }
}
