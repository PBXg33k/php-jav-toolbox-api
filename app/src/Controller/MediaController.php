<?php

namespace App\Controller;

use App\Entity\Title;
use App\Repository\TitleRepository;
use App\Service\JAVProcessorService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MediaController extends Controller
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
