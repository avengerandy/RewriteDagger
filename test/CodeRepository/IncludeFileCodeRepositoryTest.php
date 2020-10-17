<?php declare(strict_types=1);

    // mock php buildin function by namespace
    require_once(__DIR__ . '/mock/mockBuildinFunction.php');

    use PHPUnit\Framework\TestCase;
    use RewriteDagger\CodeRepository\FileCodeRepository;
    use RewriteDagger\CodeRepository\CodeRepositoryInterface;
    use RewriteDagger\CodeRepository\IncludeFileCodeRepository;

    final class IncludeFileCodeRepositoryTest extends TestCase
    {
        public function testInstance(): void
        {
            $this->assertInstanceOf(CodeRepositoryInterface::class, new IncludeFileCodeRepository());
            $this->assertInstanceOf(FileCodeRepository::class, new IncludeFileCodeRepository());
        }

        // test include($filePath); is real exist by expect include warning
        public function testIncludeAndEvaluateFile(): void
        {
            global $tempnamReturn;
            $tempnamReturn = 'fake file path';
            global $chmodReturn;
            $chmodReturn = true;
            global $filePutContentsReturn;
            $filePutContentsReturn = true;
            global $unlinkReturn;
            $unlinkReturn = true;
            $this->expectWarning();
            $this->expectWarningMessage("include({$tempnamReturn}): failed to open stream: No such file or directory");
            $fileCodeRepository = new IncludeFileCodeRepository('');
            $fileCodeRepository->includeCode('');
        }
    }
