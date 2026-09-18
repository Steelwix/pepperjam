<?php

    namespace App\Service;

    use AllowDynamicProperties;
    use App\Handler\Section\SectionHandlerInterface;
    use App\Handler\Section\ShowreelSectionHandler;

    #[AllowDynamicProperties]
    class SectionService
    {
        const SHOWREEL = 'showreel';
        public function __construct(
            ShowreelSectionHandler $showreelSectionHandler
        ){
            $this->showreelSectionHandler = $showreelSectionHandler;
        }

        public function getHandlerByType(string $sectionType): SectionHandlerInterface
        {
            switch ($sectionType) {
                case self::SHOWREEL:
                    return $this->showreelSectionHandler;

                default:
                    throw new \InvalidArgumentException(
                        sprintf('Type de section inconnu : "%s"', $sectionType)
                    );
            }
        }
    }
