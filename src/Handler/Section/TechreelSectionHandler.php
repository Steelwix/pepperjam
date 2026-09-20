<?php

    namespace App\Handler\Section;

    use App\Entity\Media;
    use App\Entity\Section;
    use App\Form\MediaType;
    use App\Form\Section\SectionShowreelType;
    use App\Service\MediaService;
    use Doctrine\ORM\EntityManagerInterface;
    use Random\RandomException;
    use Symfony\Bundle\SecurityBundle\Security;
    use Symfony\Component\Form\FormFactoryInterface;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
    use Symfony\UX\Turbo\TurboBundle;
    use Twig\Environment;
    use Twig\Error\LoaderError;
    use Twig\Error\RuntimeError;
    use Twig\Error\SyntaxError;

    class TechreelSectionHandler implements SectionHandlerInterface
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
         * @throws RuntimeError
         * @throws LoaderError
         * @throws RandomException
         */
        public function handle(Request $request): Response
        {
            $section = $this->em
                ->getRepository(Section::class)
                ->findOneBy(['type' => Section::TECHREEL_TYPE]);

            if (!$section) {
                $section = (new Section())
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setEnabled(true)
                    ->setType(Section::TECHREEL_TYPE)
                    ->setUpdatedBy($this->security->getUser());

                $this->em->persist($section);
            }

            $form = $this->formFactory->create(MediaType::class);
            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) {


                $this->mediaService->create(
                    $form->get('media')->getData(),
                    $section, $section->getMedia()->first()?: null
                );

                $this->em->flush();
            }
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return new Response(
                $this->twig->render('home/section/showreel.html.twig', [
                    'media' => $section->getMedia()->first(),
                    'form' => $form->createView(),
                    'spot' => Section::TECHREEL_TYPE
                ])
            );
        }
    }
