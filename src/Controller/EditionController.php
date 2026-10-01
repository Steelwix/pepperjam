<?php

    namespace App\Controller;

    use AllowDynamicProperties;
    use App\Factory\SectionHandlerFactory;
    use App\Service\SectionService;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Security\Http\Attribute\IsGranted;
    use Symfony\Component\Stopwatch\Section;
    use Symfony\UX\Turbo\TurboBundle;

    #[AllowDynamicProperties] class EditionController extends AbstractController
    {
        public function __construct(private  readonly SectionHandlerFactory $sectionHandlerFactory, private readonly EntityManagerInterface $em){
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

    }
