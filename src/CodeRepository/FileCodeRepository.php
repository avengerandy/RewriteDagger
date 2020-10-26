<?php declare(strict_types=1);

    namespace RewriteDagger\CodeRepository;

    abstract class FileCodeRepository implements CodeRepositoryInterface
    {
        private $tempPath = null;

        public function __construct(string $tempPath = null)
        {
            $this->tempPath = $tempPath ?? sys_get_temp_dir();
        }

        public function getTempPath(): string
        {
            return $this->tempPath;
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

            $this->includeAndEvaluateFile($tempFilePath);
            if (!unlink($tempFilePath)) {
                throw new \RuntimeException("Could not delete file: {$tempFilePath}");
            }
        }

        abstract protected function includeAndEvaluateFile(string $filePath): void;

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
