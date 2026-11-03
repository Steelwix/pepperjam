<?php

namespace App\Handler\Section;

use App\Entity\Section;
use App\Entity\Software;
use App\Form\Section\SoftwareType;
use App\Service\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Turbo\TurboBundle;
use Twig\Environment;

class SoftwareSectionHandler implements SectionHandlerInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly FormFactoryInterface $formFactory,
        private readonly MediaService $mediaService,
    ) {
    }

    public function handle(Request $request): Response
    {
        $section = $this->em->getRepository(Section::class)->findOneBy(['type' => Section::SOFTWARES_TYPE]);
        if (!$section) {
            $section = (new Section())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setEnabled(true)
                ->setType(Section::SOFTWARES_TYPE)
                ->setUpdatedBy($this->security->getUser());
            $this->em->persist($section);
        }

        $id = $request->query->get('id');
        $software = $id ? $this->em->getRepository(Software::class)->find($id) : new Software();
        if (!$software) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $form = $this->formFactory->create(SoftwareType::class, $software, [
            'image_required' => $software->getMedia()->isEmpty(),
        ]);
        $form->handleRequest($request);
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        if ($form->isSubmitted() && $form->isValid()) {
            $upload = $form->get('media')->get('media')->getData();
            if ($upload !== null) {
                $this->mediaService->replaceForSoftware($upload, $software);
            }
            $this->em->persist($software);
            $this->em->flush();

            return new Response($this->twig->render('home/section/turbo-load/softwares.html.twig', [
                'section' => $section,
                'spot' => Section::SOFTWARES_TYPE,
                'editing' => true,
            ]));
        }

        return new Response($this->twig->render('home/section/form/software-form.html.twig', [
            'software' => $software,
            'form' => $form->createView(),
            'spot' => Section::SOFTWARES_TYPE,
        ]));
    }
}
