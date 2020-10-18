<?php declare(strict_types=1);

    namespace RewriteDagger\CodeRepository;

    class EvalCodeRepository implements CodeRepositoryInterface
    {
        public function getCodeContent(string $path): string
        {
            return file_get_contents($path);
        }

        public function includeCode(string $codeContent): void
        {
            eval($codeContent);
        }
    }
