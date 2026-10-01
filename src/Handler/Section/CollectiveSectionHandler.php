<?php

    namespace App\Handler\Section;

    use App\Entity\Section;
    use App\Form\MediaType;
    use App\Form\Section\CollectiveType;
    use App\Service\MediaService;
    use App\Service\SectionService;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\SecurityBundle\Security;
    use Symfony\Component\Form\FormFactoryInterface;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\UX\Turbo\TurboBundle;
    use Twig\Environment;

    class CollectiveSectionHandler implements SectionHandlerInterface
    {

        public function __construct(
            private readonly Environment $twig,
            private readonly EntityManagerInterface $em,
            private readonly Security $security,
            private readonly FormFactoryInterface $formFactory,
            private readonly MediaService $mediaService,
            private readonly SectionService $sectionService,

        ) {
        }

        public function handle(Request $request): Response
        {
            $section = $this->em
                ->getRepository(Section::class)
                ->findOneBy(['type' => Section::COLLECTIVE_TYPE]);

            if (!$section) {
                $section = (new Section())
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setEnabled(true)
                    ->setType(Section::COLLECTIVE_TYPE)
                    ->setUpdatedBy($this->security->getUser());

                $this->em->persist($section);
            }

            $form = $this->formFactory->create(CollectiveType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();
                $this->mediaService->create(
                    $formData['media']['media'],
                    $section, $section->getMedia()->first()?: null
                );
                unset($formData['media']);

                $data = $this->sectionService->buildData($formData);
                $section->setData($data);

            }
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return new Response(
                $this->twig->render('home/section/collective.html.twig', [
                    'media' => $section->getMedia()->first(),
                    'form' => $form->createView(),
                    'spot' => Section::COLLECTIVE_TYPE
                ])
            );
        }
    }
