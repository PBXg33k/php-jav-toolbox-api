<?php

namespace App\Controller;

use App\Entity\Title;
use App\Repository\TitleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MediaController extends Controller
{
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
}
