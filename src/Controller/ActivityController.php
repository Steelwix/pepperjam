<?php

    namespace App\Controller;

    use AllowDynamicProperties;
    use App\Entity\Activity;
    use App\Entity\Project;
    use App\Entity\Section;
    use App\Form\Section\ProjectType;
    use App\Repository\ActivityRepository;
    use App\Service\MediaService;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Security\Http\Attribute\IsGranted;
    use Symfony\UX\Turbo\TurboBundle;

    #[AllowDynamicProperties] class ActivityController extends AbstractController
    {
        public function __construct(){}



        #[Route('/activity/{id}', name: 'app_activity_view')]
        public function editSection(Request $request, Activity $activity, ActivityRepository $activityRepository): Response
        {
            $projects = $activity->getProjects();
            $editing = $request->query->getBoolean('editing');
            if ($editing && !$this->isGranted('ROLE_ADMIN')) {
                $editing = false;
            }
            return $this->render('home/activity/display.html.twig', [
                'projects' => $projects,
                'activity' => $activity,
                'activities' => $activityRepository->findBy([], ['title' => 'ASC']),
                'editing' => $editing,
            ]);
        }

        #[Route('/activity/project/create', name: 'app_activity_project_create')]
        #[IsGranted('ROLE_ADMIN')]
        public function createproject(
            Request $request,
            EntityManagerInterface $entityManager,
            MediaService $mediaService,
        ): Response
        {
            $project = new Project();
            $form = $this->createForm(ProjectType::class, $project);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($project);

                $file = $form->get('mediaFile')->getData();
                if ($file !== null) {
                    $mediaService->createForProject($file, $project);
                }

                $entityManager->flush();

                return $this->redirectToRoute('app_activity_view', [
                    'id' => $project->getActivity()->getId(),
                ]);
            }

            return $this->render('home/activity/form.html.twig', [
                'project' => $project,
                'form' => $form->createView(),
            ]);
        }

        #[Route('/activity/project/{id}/edit', name: 'app_activity_project_edit', methods: ['GET', 'POST'])]
        #[IsGranted('ROLE_ADMIN')]
        public function editProject(
            Request $request,
            Project $project,
            EntityManagerInterface $entityManager,
            MediaService $mediaService,
        ): Response {
            $form = $this->createForm(ProjectType::class, $project, [
                'require_media' => false,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $file = $form->get('mediaFile')->getData();
                if ($file !== null) {
                    $mediaService->replaceForProject($file, $project);
                }

                $entityManager->flush();

                return $this->redirectToRoute('app_activity_view', [
                    'id' => $project->getActivity()->getId(),
                    'editing' => true,
                ]);
            }

            return $this->render('home/activity/form.html.twig', [
                'project' => $project,
                'form' => $form->createView(),
            ]);
        }

        #[Route('/activity/project/{id}/delete', name: 'app_activity_project_delete', methods: ['POST'])]
        #[IsGranted('ROLE_ADMIN')]
        public function deleteProject(
            Request $request,
            Project $project,
            EntityManagerInterface $entityManager,
            MediaService $mediaService,
        ): RedirectResponse {
            if (!$this->isCsrfTokenValid('delete-project-'.$project->getId(), $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Jeton CSRF invalide.');
            }

            $activityId = $project->getActivity()?->getId();
            foreach ($project->getMedia() as $media) {
                $mediaService->remove($media);
            }

            $entityManager->remove($project);
            $entityManager->flush();

            if ($activityId === null) {
                return $this->redirectToRoute('app_home', ['editing' => true]);
            }

            return $this->redirectToRoute('app_activity_view', [
                'id' => $activityId,
                'editing' => true,
            ]);
        }

    }
