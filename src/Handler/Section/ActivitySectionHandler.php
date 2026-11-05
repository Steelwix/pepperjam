<?php

    namespace App\Handler\Section;

    use App\Entity\Activity;
    use App\Entity\Section;
    use App\Form\Section\ActivityType;
    use App\Service\MediaService;
    use Doctrine\ORM\EntityManagerInterface;
    use Random\RandomException;
    use Symfony\Bundle\SecurityBundle\Security;
    use Symfony\Component\Form\FormFactoryInterface;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\UX\Turbo\TurboBundle;
    use Twig\Environment;
    use Twig\Error\LoaderError;
    use Twig\Error\RuntimeError;
    use Twig\Error\SyntaxError;

    class ActivitySectionHandler implements SectionHandlerInterface
    {
        public function __construct(
            private readonly Environment $twig,
            private readonly EntityManagerInterface $em,
            private readonly Security $security,
            private readonly FormFactoryInterface $formFactory,
            private readonly MediaService $mediaService,

        ) {
        }

        /**
         * @throws SyntaxError
         * @throws RandomException
         * @throws RuntimeError
         * @throws LoaderError
         */
        public function handle(Request $request): Response
        {
            $section = $this->em
                ->getRepository(Section::class)
                ->findOneBy(['type' => Section::ACTIVITIES_TYPE]);

            if (!$section) {
                $section = (new Section())
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setEnabled(true)
                    ->setType(Section::ACTIVITIES_TYPE)
                    ->setUpdatedBy($this->security->getUser());

                $this->em->persist($section);
            }
            $id = $request->query->get('id');
            if (!$id) {
                $activity = new Activity();
            } else {
                $activity = $this->em->getRepository(Activity::class)->find($id);
                if (!$activity) {
                    throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
                }
            }

            $form = $this->formFactory->create(
                ActivityType::class,
                $activity,
                ['image_required' => $activity->getMedia()->isEmpty()]
            );
            $form->handleRequest($request);
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            if ($form->isSubmitted() && $request->request->getBoolean('_cancel')) {
                return new Response($this->twig->render('home/section/turbo-load/activities.html.twig', [
                    'section' => $section,
                    'spot' => Section::ACTIVITIES_TYPE,
                    'editing' => true,
                ]));
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $upload = $form->get('media')->get('media')->getData();
                if ($upload !== null) {
                    $media = $this->mediaService->create(
                        $upload,
                        $section,
                        $activity->getMedia()->first() ?: null
                    );
                    $activity->addMedium($media);
                }

                $this->em->persist($activity);

                $this->em->flush();
                return new Response(
                    $this->twig->render('home/section/turbo-load/activities.html.twig', [
                        'section' => $section,
                        'spot' => Section::ACTIVITIES_TYPE,
                        'editing' => true
                    ])
                );
            }

            return new Response(
                $this->twig->render('home/section/form/activities-form.html.twig', [
                    'section' => $section,
                    'activity' => $activity,
                    'form' => $form->createView(),
                    'spot' => Section::ACTIVITIES_TYPE
                ])
            );
        }
    }
