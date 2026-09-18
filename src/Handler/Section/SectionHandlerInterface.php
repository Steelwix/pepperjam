<?php

    namespace App\Handler\Section;

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    interface SectionHandlerInterface
    {
        public function handle(Request $request): Response;
    }
