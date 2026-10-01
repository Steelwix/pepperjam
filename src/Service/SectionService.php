<?php

    namespace App\Service;

    use AllowDynamicProperties;
    use App\Entity\Section;
    use App\Handler\Section\CollectiveSectionHandler;
    use App\Handler\Section\SectionHandlerInterface;
    use App\Handler\Section\ShowreelSectionHandler;
    use App\Handler\Section\TechreelSectionHandler;

    #[AllowDynamicProperties]
    class SectionService
    {

        public function __construct(){
        }

        public function buildData(array $data): array
        {
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $data[$key] = $this->handleTextArea($value);
                }
            }
            return $data;
        }

        public function handleTextArea(string $text): string
        {
            return preg_replace('/<\/?div[^>]*>/i', '', $text);
        }
    }
