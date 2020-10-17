<?php declare(strict_types=1);

    // mock php buildin function by namespace
    require_once(__DIR__ . '/mock/mockBuildinFunction.php');

    use PHPUnit\Framework\TestCase;
    use RewriteDagger\CodeRepository\FileCodeRepository;
    use RewriteDagger\CodeRepository\CodeRepositoryInterface;

    // fake FileCodeRepository that can perceive includeAndEvaluateFile operation
    class PerceiveFileCodeRepository extends FileCodeRepository
    {
        public $filePath = '';

        protected function includeAndEvaluateFile(string $filePath): void
        {
            $this->filePath = $filePath;
        }
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
            global $tempnamReturn;
            $tempnamReturn = '';
            global $chmodReturn;
            $chmodReturn = false;
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Could not chmod file: ');
            $fileCodeRepository = new PerceiveFileCodeRepository('');
            $fileCodeRepository->includeCode('');
        }

        public function testIncludeCodeCouldNotWrite(): void
        {
            global $tempnamReturn;
            $tempnamReturn = '';
            global $chmodReturn;
            $chmodReturn = true;
            global $filePutContentsReturn;
            $filePutContentsReturn = false;
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Could not write file: ');
            $fileCodeRepository = new PerceiveFileCodeRepository('');
            $fileCodeRepository->includeCode('');
        }

        public function testIncludeCode(): void
        {
            global $tempnamReturn;
            $tempnamReturn = 'fake file path';
            global $chmodReturn;
            $chmodReturn = true;
            global $filePutContentsReturn;
            $filePutContentsReturn = true;
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
            global $chmodReturn;
            $chmodReturn = true;
            global $filePutContentsReturn;
            $filePutContentsReturn = true;
            global $unlinkReturn;
            $unlinkReturn = false;
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Could not delete file: ');
            $fileCodeRepository = new PerceiveFileCodeRepository('');
            $fileCodeRepository->includeCode('');
        }
    }
