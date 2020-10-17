<?php declare(strict_types=1);

    // mock php buildin function by namespace
    namespace RewriteDagger\CodeRepository;

    use PHPUnit\Framework\TestCase;

    // fake FileCodeRepository that can perceive includeAndEvaluateFile operation
    class PerceiveFileCodeRepository extends FileCodeRepository
    {
        public $filePath = '';

        protected function includeAndEvaluateFile(string $filePath): void
        {
            $this->filePath = $filePath;
        }
    }

    $tempGlobalVar = 0;

    $fileGetContentsReturn = '';
    function file_get_contents(): string
    {
        global $fileGetContentsReturn;
        return $fileGetContentsReturn;
    }

    function umask() {}

    $tempnamReturn = '';
    function tempnam(): string
    {
        global $tempnamReturn;
        return $tempnamReturn;
    }

    $chmodReturn = false;
    function chmod(): bool
    {
        global $chmodReturn;
        return $chmodReturn;
    }

    $filePutContentsReturn = false;
    function file_put_contents(): bool
    {
        global $filePutContentsReturn;
        return $filePutContentsReturn;
    }

    $unlinkReturn = false;
    function unlink(): bool
    {
        global $unlinkReturn;
        return $unlinkReturn;
    }

    final class FileCodeRepositoryTest extends TestCase
    {
        public function testInstance(): void
        {
            $this->assertInstanceOf(CodeRepositoryInterface::class, new PerceiveFileCodeRepository());
        }

        public function testConstruct(): void
        {
            $fileCodeRepository = new PerceiveFileCodeRepository('');
            $this->assertSame('', $fileCodeRepository->getTempPath());
            $fileCodeRepository = new PerceiveFileCodeRepository('tempPath');
            $this->assertSame('tempPath', $fileCodeRepository->getTempPath());
            $fileCodeRepository = new PerceiveFileCodeRepository();
            $this->assertSame(sys_get_temp_dir(), $fileCodeRepository->getTempPath());
        }

        public function testGetCodeContent(): void
        {
            global $fileGetContentsReturn;
            $fileGetContentsReturn = 'mock_file_get_contents';
            $fileCodeRepository = new PerceiveFileCodeRepository('');
            $this->assertSame('mock_file_get_contents', $fileCodeRepository->getCodeContent(''));
        }

        public function testIncludeCodeCouldNotChmod(): void
        {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Could not chmod file: ');
            $fileCodeRepository = new PerceiveFileCodeRepository('');
            $fileCodeRepository->includeCode('');
        }

        public function testIncludeCodeCouldNotWrite(): void
        {
            global $chmodReturn;
            $chmodReturn = true;
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Could not write file: ');
            $fileCodeRepository = new PerceiveFileCodeRepository('');
            $fileCodeRepository->includeCode('');
        }

        public function testIncludeCode(): void
        {
            global $filePutContentsReturn;
            $filePutContentsReturn = true;
            global $tempnamReturn;
            $tempnamReturn = 'fake file path';
            global $unlinkReturn;
            $unlinkReturn = true;
            $fileCodeRepository = new PerceiveFileCodeRepository('');
            $fileCodeRepository->includeCode('');
            $this->assertSame('fake file path', $fileCodeRepository->filePath);
        }

        public function testIncludeCodeCouldNotDelete(): void
        {
            global $tempnamReturn;
            $tempnamReturn = '';
            global $unlinkReturn;
            $unlinkReturn = false;
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Could not delete file: ');
            $fileCodeRepository = new PerceiveFileCodeRepository('');
            $fileCodeRepository->includeCode('');
        }
    }
