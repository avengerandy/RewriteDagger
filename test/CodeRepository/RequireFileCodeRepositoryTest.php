<?php declare(strict_types=1);

    // mock php buildin function by namespace
    require_once(__DIR__ . '/mock/mockBuildinFunction.php');

    use PHPUnit\Framework\TestCase;
    use RewriteDagger\Dagger;
    use RewriteDagger\CodeRepository\FileCodeRepository;
    use RewriteDagger\CodeRepository\CodeRepositoryInterface;
    use RewriteDagger\CodeRepository\EvalCodeRepository;
    use RewriteDagger\CodeRepository\RequireFileCodeRepository;

    final class RequireFileCodeRepositoryTest extends TestCase
    {
        public function testInstance(): void
        {
            $this->assertInstanceOf(CodeRepositoryInterface::class, new RequireFileCodeRepository(''));
            $this->assertInstanceOf(FileCodeRepository::class, new RequireFileCodeRepository(''));
        }

        public function testIncludeAndEvaluateFile(): void
        {
            global $tempnamReturn;
            $tempnamReturn = __DIR__ . '/mock/mockCodeFile.php';
            global $chmodReturn;
            $chmodReturn = true;
            global $filePutContentsReturn;
            $filePutContentsReturn = true;
            global $unlinkReturn;
            $unlinkReturn = true;
            global $varFromMockCodeFile;
            $varFromMockCodeFile = 0;
            $fileCodeRepository = new RequireFileCodeRepository('');
            $fileCodeRepository->includeCode('');
            $this->assertSame(42, $varFromMockCodeFile);
        }
    }
