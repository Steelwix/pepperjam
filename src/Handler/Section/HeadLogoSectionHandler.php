<?php

namespace App\Handler\Section;

use App\Entity\Section;
use App\Form\Section\HeadLogoType;
use App\Service\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Turbo\TurboBundle;
use Twig\Environment;

class HeadLogoSectionHandler implements SectionHandlerInterface
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
        $section = $this->em->getRepository(Section::class)->findOneBy([
            'type' => Section::HEAD_LOGO_TYPE,
        ]);

        if (!$section) {
            $section = (new Section())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setEnabled(true)
                ->setType(Section::HEAD_LOGO_TYPE)
                ->setUpdatedBy($this->security->getUser());
            $this->em->persist($section);
        }

        $showreel = $this->em->getRepository(Section::class)->findOneBy([
            'type' => Section::SHOWREEL_TYPE,
        ]);
        $legacySettings = $showreel?->getData() ?? [];
        $logoSettings = $section->getData() ?? [];

        $form = $this->formFactory->create(HeadLogoType::class, $section, [
            'logo_required' => $section->getMedia()->isEmpty(),
        ]);
        $form->get('logoAutoHide')->setData($logoSettings['logoAutoHide'] ?? $legacySettings['logoAutoHide'] ?? true);
        $form->get('logoDisplayDuration')->setData($logoSettings['logoDisplayDuration'] ?? $legacySettings['logoDisplayDuration'] ?? 3);
        $form->handleRequest($request);
        $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

        if ($form->isSubmitted() && $request->request->getBoolean('_cancel')) {
            return new Response($this->twig->render('home/section/turbo-load/showreel.html.twig'));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $logoSettings['logoAutoHide'] = (bool) $form->get('logoAutoHide')->getData();
            $logoSettings['logoDisplayDuration'] = (float) $form->get('logoDisplayDuration')->getData();
            $section->setData($logoSettings);

            if ($showreel && $showreel->getData() !== null) {
                $legacySettings = $showreel->getData();
                unset($legacySettings['logoAutoHide'], $legacySettings['logoDisplayDuration']);
                $showreel->setData($legacySettings ?: null);
            }

            $logo = $form->get('logo')->getData();
            if ($logo !== null) {
                $this->mediaService->create(
                    $logo,
                    $section,
                    $section->getMedia()->first() ?: null,
                );
            }

            return new Response($this->twig->render('home/section/turbo-load/showreel.html.twig'));
        }

        return new Response($this->twig->render('home/section/form/head-logo-form.html.twig', [
            'section' => $section,
            'logo' => $section->getMedia()->first() ?: null,
            'form' => $form->createView(),
        ]));
    }
}
