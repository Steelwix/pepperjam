<?php
namespace App\Twig;


    use App\Entity\Member;
    use App\Entity\Section;
    use Doctrine\ORM\EntityManagerInterface;
    use Twig\Extension\AbstractExtension;
    use Twig\TwigFunction;

    final class SectionExtension extends AbstractExtension
    {
        public function __construct(
            private readonly EntityManagerInterface $em
        ) {
        }

        public function getFunctions(): array
        {
            return [
                new TwigFunction('getSectionByName', [$this, 'getSectionByName']),
                new TwigFunction('getTeamMembers', [$this, 'getTeamMembers']),
            ];
        }

        public function getSectionByName(string $type): ?Section
        {
            return $this->em->getRepository(Section::class)->findOneBy([
                'type'    => $type,
                'enabled' => true,
            ]);
        }

        public function getTeamMembers(): array
        {
            return $this->em->getRepository(Member::class)->findBy([]);
        }
    }
