<?php

namespace App\Controller;

use App\Card\CardGraphic;
use App\Card\CardHand;
use App\Card\DeckOfCards;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    #[Route("/proj", name: "proj_home")]
    public function home(): Response
    {
        return $this->render('project/home.html.twig');
    }

    #[Route("/proj/about", name: "about")]
    public function about(): Response
    {
        return $this->render('project/about.html.twig');
    }

}