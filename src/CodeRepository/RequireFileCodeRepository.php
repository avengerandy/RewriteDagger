<?php declare(strict_types=1);

    namespace RewriteDagger\CodeRepository;

    class RequireFileCodeRepository extends FileCodeRepository
    {
        protected function includeAndEvaluateFile(string $filePath): void
        {
            // every $filePath is unique that generate by tempnam
            require($filePath);
        }
    }
