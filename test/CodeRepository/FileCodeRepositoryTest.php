<?php declare(strict_types=1);

    // mock php buildin function by namespace
    namespace RewriteDagger\CodeRepository;

    use PHPUnit\Framework\TestCase;

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
            $this->assertInstanceOf(CodeRepositoryInterface::class, new FileCodeRepository());
        }

        public function testConstruct(): void
        {
            $fileCodeRepository = new FileCodeRepository('');
            $this->assertSame('', $fileCodeRepository->getTempPath());
            $fileCodeRepository = new FileCodeRepository('tempPath');
            $this->assertSame('tempPath', $fileCodeRepository->getTempPath());
            $fileCodeRepository = new FileCodeRepository();
            $this->assertSame(sys_get_temp_dir(), $fileCodeRepository->getTempPath());
        }

        public function testGetCodeContent(): void
        {
            global $fileGetContentsReturn;
            $fileGetContentsReturn = 'mock_file_get_contents';
            $fileCodeRepository = new FileCodeRepository('');
            $this->assertSame('mock_file_get_contents', $fileCodeRepository->getCodeContent(''));
        }

        public function testIncludeCodeCouldNotChmod(): void
        {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Could not chmod file: ');
            $fileCodeRepository = new FileCodeRepository('');
            $fileCodeRepository->includeCode('');
        }

        public function testIncludeCodeCouldNotWrite(): void
        {
            global $chmodReturn;
            $chmodReturn = true;
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage("Could not write file: ");
            $fileCodeRepository = new FileCodeRepository('');
            $fileCodeRepository->includeCode('');
        }

        public function testIncludeCode(): void
        {
            global $filePutContentsReturn;
            $filePutContentsReturn = true;
            global $tempnamReturn;
            $tempnamReturn = __DIR__ . '/testData/testScript.php';
            global $unlinkReturn;
            $unlinkReturn = true;
            global $tempGlobalVar;
            $fileCodeRepository = new FileCodeRepository('');
            $fileCodeRepository->includeCode('');
            $this->assertSame(42, $tempGlobalVar);
        }

        public function testIncludeCodeCouldNotDelete(): void
        {
            global $tempnamReturn;
            global $unlinkReturn;
            $unlinkReturn = false;
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage("Could not delete file: {$tempnamReturn}");
            $fileCodeRepository = new FileCodeRepository('');
            $fileCodeRepository->includeCode('');
        }
    }
