<?php declare(strict_types=1);

    namespace RewriteDagger\CodeRepository;

    class FileCodeRepository implements CodeRepositoryInterface
    {
        private $tempPath = null;

        function __construct(string $tempPath = null)
        {
            $this->tempPath = $tempPath ?? sys_get_temp_dir();
        }

        public function getCodeContent(string $path): string
        {
            return file_get_contents($path);
        }

        public function includeCode(string $codeContent): void
        {
            $tempFilePath = $this->generateTempFile();
            if (!(bool) file_put_contents($tempFilePath, $codeContent)) {
                throw new \RuntimeException("Could not write file: {$tempFilePath}");
            }

            include_once($tempFilePath);
            if (!unlink($tempFilePath)) {
                throw new \RuntimeException("Could not delete file: {$tempFilePath}");
            }
        }

        public function getTempPath(): string
        {
            return $this->tempPath;
        }

        private function generateTempFile(): string
        {
            $oldUmask = umask(0);
            $filePath = tempnam($this->tempPath, 'RewriteDagger');
            if (!chmod($filePath, 0644)) {
                throw new \RuntimeException("Could not chmod file: {$filePath}");
            }
            umask($oldUmask);
            return $filePath;
        }
    }
