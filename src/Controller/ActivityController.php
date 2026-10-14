<?php

    namespace App\Controller;

    use AllowDynamicProperties;
    use App\Entity\Activity;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Security\Http\Attribute\IsGranted;
    use Symfony\UX\Turbo\TurboBundle;

    #[AllowDynamicProperties] class ActivityController extends AbstractController
    {
        public function __construct(){}

        #[Route('/activity/{id}', name: 'app_activity_view')]
        public function editSection(Request $request, Activity $activity): Response
        {
            $projects = $activity->getProjects();

            return $this->render('home/activity/display.html.twig', [$projects]);
        }
    }
