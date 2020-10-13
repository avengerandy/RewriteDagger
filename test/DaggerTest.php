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
        public function testAddDeleteRule(): void
        {
            $codeRepository = new PerceiveCodeRepository();
            $dagger = new Dagger($codeRepository);
            $dagger->addDeleteRule('not found string', 'nothing');
            $dagger->addDeleteRule('is a number.');
            $dagger->includeCode('');
            $this->assertSame('42 ', $codeRepository->includeCodeInput);
        }

        public function testAddStringReplaceRule(): void
        {
            $codeRepository = new PerceiveCodeRepository();
            $dagger = new Dagger($codeRepository);
            $dagger->addStringReplaceRule('not found string', 'nothing');
            $dagger->addStringReplaceRule('is a number', ': Answer to the Ultimate Question of Everything');
            $dagger->includeCode('');
            $this->assertSame('42 : Answer to the Ultimate Question of Everything.', $codeRepository->includeCodeInput);
        }

        public function testAddRegexReplaceRule(): void
        {
            $codeRepository = new PerceiveCodeRepository();
            $dagger = new Dagger($codeRepository);
            $dagger->addRegexReplaceRule('/not found string/', 'nothing');
            $dagger->addRegexReplaceRule('/\d+/', 'Number');
            $dagger->includeCode('');
            $this->assertSame('Number is a number.', $codeRepository->includeCodeInput);
        }

        public function testAddRegexReplaceCallbackRule(): void
        {
            $codeRepository = new PerceiveCodeRepository();
            $dagger = new Dagger($codeRepository);
            $dagger->addRegexReplaceCallbackRule('/not found string/', function () {
                return 'nothing';
            });
            $dagger->addRegexReplaceCallbackRule('/^(\d+).*(number)\.$/', function ($match) {
                return "[{$match[1]}] is a ({$match[2]}).";
            });
            $dagger->includeCode('');
            $this->assertSame('[42] is a (number).', $codeRepository->includeCodeInput);
        }
    }