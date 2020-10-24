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

        /*
         * phpunit cannot except require error
         * require produce a fatal E_COMPILE_ERROR level error that cannot be handled with a user defined function.
         * https://www.php.net/manual/en/function.require.php
         * https://www.php.net/manual/en/function.set-error-handler.php
         *
         * since require is syntax not function, its cannot mock by namespace too
         * so use Dagger to rewrite RequireFileCodeRepository
         * test require($filePath); is real exist (can replaced) and perceive its input
         */
        public function testIncludeAndEvaluateFileError(): void
        {
            // use real file_get_contents for EvalCodeRepository rewrite RequireFileCodeRepository
            $filePath = __DIR__ . '/../../src/CodeRepository/RequireFileCodeRepository.php';
            global $fileGetContentsReturn;
            $fileGetContentsReturn = \file_get_contents($filePath);

            $dagger = new Dagger(new EvalCodeRepository());
            $dagger->addDeleteRule('<?php declare(strict_types=1);');
            $dagger->addReplaceRule('RequireFileCodeRepository', 'DaggerRequireFileCodeRepository');
            $dagger->addReplaceRule('require($filePath);', 'global $mockRequirePath; $mockRequirePath = $filePath;');
            $dagger->includeCode(''); // already mock file_get_contents

            global $tempnamReturn;
            $tempnamReturn = 'fake file path';
            global $chmodReturn;
            $chmodReturn = true;
            global $filePutContentsReturn;
            $filePutContentsReturn = true;
            global $unlinkReturn;
            $unlinkReturn = true;
            $fileCodeRepository = new \RewriteDagger\CodeRepository\DaggerRequireFileCodeRepository('');
            $fileCodeRepository->includeCode('');
            global $mockRequirePath;
            $this->assertSame('fake file path', $mockRequirePath);
        }
    }
