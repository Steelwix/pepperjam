<?php

    namespace App\Handler\Section;

    use App\Entity\Media;
    use App\Entity\Section;
    use App\Form\MediaType;
    use App\Form\Section\SectionShowreelType;
    use Doctrine\ORM\EntityManagerInterface;
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

    class ShowreelSectionHandler implements SectionHandlerInterface
    {


        public function __construct(
            private readonly Environment $twig,
            private readonly EntityManagerInterface $em,
            private readonly Security $security,
            private readonly FormFactoryInterface $formFactory,

        ) {
        }

        /**
         * @throws SyntaxError
         * @throws RuntimeError
         * @throws LoaderError
         */
        public function handle(Request $request): Response
        {
            // Exemple de données par défaut
            $section = $this->em->getRepository(Section::class)->findOneBy(['type' => Section::SHOWREEL_TYPE]);
            if (!$section) {
                $section = (new Section())->setCreatedAt(new \DateTimeImmutable())->setEnabled(true)->setType(
                    Section::SHOWREEL_TYPE
                )->setUpdatedBy($this->security->getUser());
            }

            $media = $section->getMedia()->first();

            if (!$media) {
                $media = new Media();
                $section->addMedium($media);
            }

            $form = $this->formFactory->create(MediaType::class);
            $form->handleRequest($request);
            // Traitement de la requête POST
            if ($form->isSubmitted() && $form->isValid()) {
                dd($form->getData());
            }
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            return new Response(
                $this->twig->render('home/section/showreel.html.twig', [
                    'media' => $media,
                    'form' => $form->createView(),
                ])
            );
        }
    }
