<?php declare(strict_types=1);

    // mock php buildin function by namespace
    require_once(__DIR__ . '/mock/mockBuildinFunction.php');

    use PHPUnit\Framework\TestCase;
    use RewriteDagger\CodeRepository\CodeRepositoryInterface;
    use RewriteDagger\CodeRepository\EvalCodeRepository;

    final class EvalCodeRepositoryTest extends TestCase
    {
        public function testInstance(): void
        {
            $this->assertInstanceOf(CodeRepositoryInterface::class, new EvalCodeRepository());
        }

        public function testGetCodeContent(): void
        {
            global $fileGetContentsReturn;
            $fileGetContentsReturn = 'mock_file_get_contents';
            $evalCodeRepository = new EvalCodeRepository();
            $this->assertSame('mock_file_get_contents', $evalCodeRepository->getCodeContent(''));
        }

        public function testIncludeAndEvaluateFile(): void
        {
            $evalCodeRepository = new EvalCodeRepository();
            $evalCodeRepository->includeCode('global $mockEvalVar; $mockEvalVar = 42;');
            global $mockEvalVar;
            $this->assertSame(42, $mockEvalVar);
        }
    }
