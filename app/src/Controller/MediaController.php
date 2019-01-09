<?php

namespace App\Controller;

use App\Repository\TitleRepository;
use App\Service\JAVProcessorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MediaController extends AbstractController
{
    /**
     * @var JAVProcessorService
     */
    private $JAVProcessorService;

    public function __construct(JAVProcessorService $JAVProcessorService)
    {
        $this->JAVProcessorService = $JAVProcessorService;
    }

    /**
     * @Route("/media", name="media")
     */
    public function index(TitleRepository $titleRepository)
    {
        $titles = $titleRepository->findAll();

        return $this->json([
            'titles' => $titles
        ]);
    }

    /**
     * @Route("/media/filename/{filenameSlug}")
     */
    public function getIDFromFilename(Request $request, string $filenameSlug)
    {
        try {
            $javFile = JAVProcessorService::extractIDFromFilename($filenameSlug);

            return $this->json($javFile);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
