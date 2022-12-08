<?php

namespace App\Controller;

use App\Entity\Coffret;
use App\Repository\CoffretRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    

    #[Route(path: '/', name: 'app_home')]
    public function index(CoffretRepository $coffretRepository): Response
    {
        $coffret = $coffretRepository->findAll()?$coffretRepository->findAll()[0] : new Coffret();
        return $this->render('home/home.html.twig', [
            'coffret' => $coffret
        ]);
    }

}
