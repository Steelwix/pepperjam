<?php

    namespace App\Handler\Section;

    use App\Entity\Member;
    use App\Entity\Section;
    use App\Form\Section\MemberType;
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

    class TeamSectionHandler implements SectionHandlerInterface
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
                ->findOneBy(['type' => Section::TEAM_TYPE]);

            if (!$section) {
                $section = (new Section())
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setEnabled(true)
                    ->setType(Section::TEAM_TYPE)
                    ->setUpdatedBy($this->security->getUser());

                $this->em->persist($section);
            }
            if(!isset($datas['id']) ){
                $member = new Member();
            } else {
                $member = $this->em->getRepository(Member::class)->find($datas['id']);
            }

            $form = $this->formFactory->create(MemberType::class, $member);
            $form->handleRequest($request);
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            if ($form->isSubmitted() && $form->isValid()) {

                $media = $this->mediaService->create(
                    $form->get('media')->getData()['media'],
                    $section,  $member->getMedia()->first()?:null
                );
                $member->addMedium($media);
                $bio = $this->sectionService->handleTextArea($member->getBio());

                $data = $member->getData();
                $formData = $this->sectionService->buildData(
                    $form->get('links')->getData()
                );

                $data['links'] = $formData;

                $member->setData($data);
                $member->setBio($bio);
                $this->em->persist($member);
                return new Response(
                    $this->twig->render('home/section/turbo-load/team.html.twig', [
                        'section' => $section,
                        'form' => $form->createView(),
                        'spot' => Section::TEAM_TYPE,
                        'editing' => true
                    ])
                );
            }

            return new Response(
                $this->twig->render('home/section/add-team-member.html.twig', [
                    'section' => $section,
                    'form' => $form->createView(),
                    'spot' => Section::TEAM_TYPE
                ])
            );
        }
    }
