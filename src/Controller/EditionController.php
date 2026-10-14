<?php

    namespace App\Controller;

    use AllowDynamicProperties;
    use App\Entity\Activity;
    use App\Entity\Member;
    use App\Entity\Resound;
    use App\Entity\Section;
    use App\Factory\SectionHandlerFactory;
    use App\Service\MediaService;
    use App\Service\SectionService;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Security\Http\Attribute\IsGranted;
    use Symfony\UX\Turbo\TurboBundle;

    #[AllowDynamicProperties] class EditionController extends AbstractController
    {
        public function __construct(private  readonly SectionHandlerFactory $sectionHandlerFactory,
            private readonly EntityManagerInterface $em,
            private readonly MediaService $mediaService){
    }
        #[Route('/edit/section/{sectionType}', name: 'app_edit_section')]
        #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour intéragir avec cette route')]
        public function editSection(Request $request, string $sectionType): Response
        {
            $handler = $this->sectionHandlerFactory->getHandlerByType($sectionType);
            $return =  $handler->handle($request);
            $this->em->flush();
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return $return;
        }

        #[Route('/edit/remove/member/{id}', name: 'app_remove_member')]
        #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour intéragir avec cette route')]
        public function removeMember(Request $request, int $id): Response
        {
            $member = $this->em->getRepository(Member::class)->find($id);
            if($member === null){
                throw $this->createNotFoundException();
            }
            $this->mediaService->remove($member->getMedia()->first());
            $this->em->remove($member);
            $this->em->flush();
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            $section = $this->em
                ->getRepository(Section::class)
                ->findOneBy(['type' => Section::TEAM_TYPE]);
            return new Response(
                $this->render('home/section/turbo-load/team.html.twig', [
                    'section' => $section,
                    'spot' => Section::TEAM_TYPE,
                    'editing' => true
                ])
            );
        }


        #[Route('/edit/remove/resound/{id}', name: 'app_remove_resound')]
        #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour intéragir avec cette route')]
        public function removeResound(Request $request, int $id): Response
        {
            $resound = $this->em->getRepository(Resound::class)->find($id);
            if($resound === null){
                throw $this->createNotFoundException();
            }
            $this->mediaService->remove($resound->getMedia());
            $this->em->remove($resound);
            $this->em->flush();
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            $section = $this->em
                ->getRepository(Section::class)
                ->findOneBy(['type' => Section::RESOUND_TYPE]);
            return new Response(
                $this->render('home/section/turbo-load/resound.html.twig', [
                    'section' => $section,
                    'spot' => Section::RESOUND_TYPE,
                    'editing' => true
                ])
            );
        }

        #[Route('/edit/remove/activity/{id}', name: 'app_remove_activity')]
        #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour intéragir avec cette route')]
        public function removeActivity(Request $request, int $id): Response
        {
            $activity = $this->em->getRepository(Activity::class)->find($id);
            if($activity === null){
                throw $this->createNotFoundException();
            }
            $this->mediaService->remove($activity->getMedia()->first());
            $this->em->remove($activity);
            $this->em->flush();
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            $section = $this->em
                ->getRepository(Section::class)
                ->findOneBy(['type' => Section::ACTIVITIES_TYPE]);
            return new Response(
                $this->render('home/section/turbo-load/activities.html.twig', [
                    'section' => $section,
                    'spot' => Section::ACTIVITIES_TYPE,
                    'editing' => true
                ])
            );
        }
    }
