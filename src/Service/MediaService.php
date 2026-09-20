<?php

    namespace App\Service;

    use AllowDynamicProperties;
    use App\Entity\Media;
    use App\Entity\Section;
    use App\Repository\MediaRepository;
    use Doctrine\ORM\EntityManagerInterface;
    use Random\RandomException;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    #[AllowDynamicProperties]
    class MediaService
    {


        public function __construct(
            private readonly EntityManagerInterface $em,
            #[Autowire('%upload_directory%')] private readonly string $uploadDirectory
        ) {
        }

        /**
         * @throws RandomException
         */
        public function create(
            UploadedFile $file,
            Section $section,
            ?Media $media = null
        ): Media {
            $filename = bin2hex(random_bytes(16)) . '.' . $file->guessExtension();

            // Si le Media existe déjà, on supprime son ancien fichier physique.
            if ($media) {
                $this->removeFile($media);
            } else {
                // Aucun Media fourni → on crée l'entité.
                $media = new Media();
                $this->em->persist($media);
            }
            $media
                ->setFilename($filename)
                ->setPath('upload/')
                ->setOriginalName($file->getClientOriginalName())
                ->setMimeType($file->getMimeType())
                ->setCreatedAt(new \DateTimeImmutable());

            // Si le Media n'est pas encore lié à cette Section,
            // on l'associe.
            if (!$section->getMedia()->contains($media)) {
                $section->addMedium($media);
            }
            // Déplacement du nouveau fichier
            $file->move($this->uploadDirectory, $filename);



            return $media;
        }

        private function removeFile(Media $media): void
        {
            $path = $this->uploadDirectory.'/'. $media->getFilename();
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
