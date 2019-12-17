<?php

namespace App\Controller;

use App\Entity\JavFile;
use App\Repository\JavFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DownloadController extends AbstractController
{
    /**
     * @var JavFileRepository
     */
    private $fileRepository;

    private $XSendFileRoot;

    private $javToolboxMediaFileLocation;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $javToolboxMediaFileLocation,
        string $XSendFileRoot
    )
    {
        $this->fileRepository = $entityManager->getRepository(JavFile::class);
        $this->javToolboxMediaFileLocation = $javToolboxMediaFileLocation;
        $this->XSendFileRoot = $XSendFileRoot;
    }

    /**
     * @Route("/download/id/{id}", name="download_id")
     */
    public function downloadUsingId(int $id)
    {
        if($file = $this->fileRepository->find($id)) {
            return $this->serveDownloadUsingXSendfile($file);
        }

        throw new NotFoundHttpException();
    }

    /**
     * @Route("/download/path/{path}", name="download_path")
     */
    public function downloadUsingPath(string $path)
    {
        if($file = $this->fileRepository->findOneByPath($path)) {
            return $this->serveDownloadUsingXSendfile($file);
        }

        throw new NotFoundHttpException();
    }

    /**
     * Creates a redirect response which is picked up by nginx as a static file.
     * This results in nginx serving out the download, freeing up resources for PHP
     *
     * @param JavFile $javFile
     * @return RedirectResponse
     */
    private function serveDownloadUsingXSendfile(JavFile $javFile)
    {
        // Replace /media with static_file_root

        $path = str_replace(
            $this->javToolboxMediaFileLocation,
            $this->XSendFileRoot,
            $javFile->getPath()
        );

        return new RedirectResponse('/', 302, [
            'X-Accel-Redirect'=> '/'.$path,
            'Content-Type' => mime_content_type($javFile->getPath()),
            'Content-Disposition' => $javFile->getFilename(),
            'Content-Length' => $javFile->getInode()->getFilesize()
        ]);
    }
}
