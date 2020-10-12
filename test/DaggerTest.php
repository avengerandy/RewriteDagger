<?php declare(strict_types=1);

    use PHPUnit\Framework\TestCase;

    use RewriteDagger\Dagger;
    use RewriteDagger\CodeRepository\CodeRepositoryInterface;

    // fake CodeRepository that can perceive includeCode input
    class PerceiveCodeRepository implements CodeRepositoryInterface
    {
        public $includeCodeInput = null;

        public function getCodeContent(string $path): string
        {
            return '42 is a number.';
        }

        public function includeCode(string $codeContent): void
        {
            $this->includeCodeInput = $codeContent;
        }
    }

    final class DaggerTest extends TestCase
    {
        public function testAddStringReplaceRule(): void
        {
            $codeRepository = new PerceiveCodeRepository();
            $dagger = new Dagger($codeRepository);
            $dagger->addStringReplaceRule('not found string', 'nothing');
            $dagger->addStringReplaceRule('is a number', ': Answer to the Ultimate Question of Life, The Universe, and Everything');
            $dagger->includeCode('');
            $this->assertSame(
                '42 : Answer to the Ultimate Question of Life, The Universe, and Everything.',
                $codeRepository->includeCodeInput
            );
        }
    }
