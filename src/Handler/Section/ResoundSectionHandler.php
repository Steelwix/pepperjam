<?php

    namespace App\Handler\Section;

    use App\Entity\Member;
    use App\Entity\Resound;
    use App\Entity\Section;
    use App\Form\Section\MemberType;
    use App\Form\Section\ResoundType;
    use App\Service\MediaService;
    use App\Service\SectionService;
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

    class ResoundSectionHandler implements SectionHandlerInterface
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

        /**
         * @throws SyntaxError
         * @throws RandomException
         * @throws RuntimeError
         * @throws LoaderError
         */
        public function handle(Request $request): Response
        {
            $datas = $request->query->all();

            $section = $this->em
                ->getRepository(Section::class)
                ->findOneBy(['type' => Section::RESOUND_TYPE]);

            if (!$section) {
                $section = (new Section())
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setEnabled(true)
                    ->setType(Section::RESOUND_TYPE)
                    ->setUpdatedBy($this->security->getUser());

                $this->em->persist($section);
            }
            if(!isset($datas['id']) ){
                $resound = new Resound();
            } else {
                $resound = $this->em->getRepository(Resound::class)->find($datas['id']);
                if (!$resound) {
                    throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
                }
            }

            $form = $this->formFactory->create(ResoundType::class, $resound, [
                'image_required' => $resound->getMedia() === null,
            ]);
            $form->handleRequest($request);
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            if ($form->isSubmitted() && $request->request->getBoolean('_cancel')) {
                return new Response($this->twig->render('home/section/turbo-load/resound.html.twig', [
                    'section' => $section,
                    'spot' => Section::RESOUND_TYPE,
                    'editing' => true,
                ]));
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $upload = $form->get('media')->get('media')->getData();
                if ($upload !== null) {
                    $media = $this->mediaService->create($upload, $section, $resound->getMedia());
                    $resound->setMedia($media);
                }


                $this->em->persist($resound);
                $this->em->flush();
                return new Response(
                    $this->twig->render('home/section/turbo-load/resound.html.twig', [
                        'section' => $section,
                        'spot' => Section::RESOUND_TYPE,
                        'editing' => true
                    ])
                );
            }

            return new Response(
                $this->twig->render('home/section/form/resound-form.html.twig', [
                    'section' => $section,
                    'resound' => $resound,
                    'form' => $form->createView(),
                    'spot' => Section::RESOUND_TYPE
                ])
            );
        }
    }
