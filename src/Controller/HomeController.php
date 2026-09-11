<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

class HomeController extends AbstractController
    {
        #[Route('/', name: 'app_home')]
        public function testTurbo(): Response
        {
            return $this->render('home/home.html.twig');
        }

    }
