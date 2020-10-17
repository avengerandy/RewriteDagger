<?php declare(strict_types=1);

    // mock php buildin function by namespace
    require_once(__DIR__ . '/mock/mockBuildinFunction.php');

    use PHPUnit\Framework\TestCase;
    use RewriteDagger\Dagger;
    use RewriteDagger\CodeRepository\FileCodeRepository;
    use RewriteDagger\CodeRepository\CodeRepositoryInterface;
    use RewriteDagger\CodeRepository\RequireFileCodeRepository;

    final class RequireFileCodeRepositoryTest extends TestCase
    {
        public function testInstance(): void
        {
            $this->assertInstanceOf(CodeRepositoryInterface::class, new RequireFileCodeRepository());
            $this->assertInstanceOf(FileCodeRepository::class, new RequireFileCodeRepository());
        }

        /*
        * phpunit cannot except require error
        * require produce a fatal E_COMPILE_ERROR level error that cannot be handled with a user defined function.
        * https://www.php.net/manual/en/function.require.php
        * https://www.php.net/manual/en/function.set-error-handler.php
        *
        * so use Dagger to rewrite RequireFileCodeRepository
        * test require($filePath); is real exist and perceive its input
        */
        public function testIncludeAndEvaluateFile(): void
        {
            $dagger = new Dagger(new class implements CodeRepositoryInterface {
                public function getCodeContent(string $path): string
                {
                    return file_get_contents($path);
                }

                public function includeCode(string $codeContent): void
                {
                    eval($codeContent);
                }
            });
            $dagger->addDeleteRule('<?php declare(strict_types=1);');
            $dagger->addReplaceRule('RequireFileCodeRepository', 'DaggerRequireFileCodeRepository');
            $dagger->addReplaceRule('require($filePath);', 'global $mockRequirePath; $mockRequirePath = $filePath;');
            $dagger->includeCode(__DIR__ . '/../../src/CodeRepository/RequireFileCodeRepository.php');

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
