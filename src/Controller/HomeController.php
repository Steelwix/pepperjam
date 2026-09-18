<?php

namespace App\Controller;

use AllowDynamicProperties;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[AllowDynamicProperties] class HomeController extends AbstractController
    {
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {

        $editing = $request->query->getBoolean('editing');
        if ($editing && !$this->isGranted('ROLE_ADMIN')) {
            $editing = false;
        }

        return $this->render('home/index.html.twig', [
            'editing' => $editing,
        ]);
    }

    }
