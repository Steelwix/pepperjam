<?php

    namespace App\Service;

    use AllowDynamicProperties;
    use App\Entity\Media;
    use App\Entity\Project;
    use App\Entity\Section;
    use App\Entity\Software;
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
            $media = $this->store($file, $media);

            // Si le Media n'est pas encore lié à cette Section,
            // on l'associe.
            if (!$section->getMedia()->contains($media)) {
                $section->addMedium($media);
            }

            return $media;
        }

        public function createForProject(UploadedFile $file, Project $project): Media
        {
            $media = $this->store($file);
            $project->addMedium($media);

            return $media;
        }

        public function createForSoftware(UploadedFile $file, Software $software): Media
        {
            $media = $this->store($file);
            $software->addMedium($media);

            return $media;
        }

        public function replaceForSoftware(UploadedFile $file, Software $software): Media
        {
            foreach ($software->getMedia()->toArray() as $existingMedia) {
                $software->removeMedium($existingMedia);
                $this->remove($existingMedia);
            }

            return $this->createForSoftware($file, $software);
        }

        public function replaceForProject(UploadedFile $file, Project $project): Media
        {
            foreach ($project->getMedia()->toArray() as $existingMedia) {
                $project->removeMedium($existingMedia);
                $this->remove($existingMedia);
            }

            return $this->createForProject($file, $project);
        }

        /**
         * @throws RandomException
         */
        private function store(UploadedFile $file, ?Media $media = null): Media
        {
            if ($media) {
                $this->removeFile($media);
            } else {
                $media = new Media();
                $this->em->persist($media);
            }

            $filename = bin2hex(random_bytes(16)) . '.' . $file->guessExtension();
            $media
                ->setFilename($filename)
                ->setPath('upload/')
                ->setOriginalName($file->getClientOriginalName())
                ->setMimeType($file->getMimeType())
                ->setCreatedAt(new \DateTimeImmutable());

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

        public function remove(Media $media): void
        {
            $this->removeFile($media);
            $this->em->remove($media);
        }
    }
