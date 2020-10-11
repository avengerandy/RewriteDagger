<?php declare(strict_types=1);

    use PHPUnit\Framework\TestCase;
    use RewriteDagger\CodeRepositoryInterface;

    class MockCodeRepository implements CodeRepositoryInterface
    {
        public function getCodeContent(string $path): string
        {
            return 'MockCodeRepository';
        }

        public function includeCode(string $codeContent): void {}
    }

    final class CodeRepositoryInterfaceTest extends TestCase
    {
        public function testInstance(): void
        {
            $this->assertInstanceOf(CodeRepositoryInterface::class, new MockCodeRepository());
        }

        /**
         * @dataProvider getMockCodeRepository
         */
        public function testCallMethod(CodeRepositoryInterface $codeRepositoryInterface): void
        {
            $this->assertEquals('MockCodeRepository', $codeRepositoryInterface->getCodeContent(''));
            $this->assertNull($codeRepositoryInterface->includeCode(''));
        }

        public function getMockCodeRepository(): array
        {
            return [[new MockCodeRepository()]];
        }
    }
