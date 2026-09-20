<?php

    namespace App\Service;

    use AllowDynamicProperties;
    use App\Entity\Section;
    use App\Handler\Section\SectionHandlerInterface;
    use App\Handler\Section\ShowreelSectionHandler;
    use App\Handler\Section\TechreelSectionHandler;

    #[AllowDynamicProperties]
    class SectionService
    {

        public function __construct(
            private readonly  ShowreelSectionHandler $showreelSectionHandler, private readonly TechreelSectionHandler $techreelSectionHandler
        ){
        }

        public function getHandlerByType(string $sectionType): SectionHandlerInterface
        {
            return match ($sectionType) {
                Section::SHOWREEL_TYPE => $this->showreelSectionHandler,
                Section::TECHREEL_TYPE => $this->techreelSectionHandler,
                default => throw new \InvalidArgumentException(
                    sprintf('Type de section inconnu : "%s"', $sectionType)
                ),
            };
        }
    }
