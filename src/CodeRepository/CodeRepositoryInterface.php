<?php declare(strict_types=1);

    namespace RewriteDagger\CodeRepository;

    interface CodeRepositoryInterface
    {
        public function getCodeContent(string $path): string;
        public function includeCode(string $codeContent): void;
    }
