<?php

    namespace App\Factory;

    use App\Entity\Section;
    use App\Handler\Section\ActivitySectionHandler;
    use App\Handler\Section\CollectiveSectionHandler;
    use App\Handler\Section\ResoundSectionHandler;
    use App\Handler\Section\SectionHandlerInterface;
    use App\Handler\Section\ShowreelSectionHandler;
    use App\Handler\Section\TeamSectionHandler;
    use App\Handler\Section\TechreelSectionHandler;

    class SectionHandlerFactory
    {
        public function __construct(
            private readonly  ShowreelSectionHandler $showreelSectionHandler,
            private readonly TechreelSectionHandler $techreelSectionHandler,
            private readonly CollectiveSectionHandler $CollectionSectionHandler,
            private readonly TeamSectionHandler $teamSectionHandler,
            private readonly ResoundSectionHandler $resoundSectionHandler,
            private readonly ActivitySectionHandler $activitiesSectionHandler,
        ){
        }

        public function getHandlerByType(string $sectionType): SectionHandlerInterface
        {
            return match ($sectionType) {
                Section::SHOWREEL_TYPE => $this->showreelSectionHandler,
                Section::TECHREEL_TYPE => $this->techreelSectionHandler,
                Section::COLLECTIVE_TYPE => $this->CollectionSectionHandler,
                Section::TEAM_TYPE => $this->teamSectionHandler,
                Section::RESOUND_TYPE => $this->resoundSectionHandler,
                Section::ACTIVITIES_TYPE => $this->activitiesSectionHandler,
                default => throw new \InvalidArgumentException(
                    sprintf('Type de section inconnu : "%s"', $sectionType)
                ),
            };
        }
    }
