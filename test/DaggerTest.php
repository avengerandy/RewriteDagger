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
            $dagger->addDeleteRule('not found string');
            $dagger->addDeleteRule('is a number.');
            $dagger->includeCode('');
            $this->assertSame('42 ', $codeRepository->includeCodeInput);
        }

        public function testAddRegexDeleteRule(): void
        {
            $codeRepository = new PerceiveCodeRepository();
            $dagger = new Dagger($codeRepository);
            $dagger->addRegexDeleteRule('/not found string/');
            $dagger->addRegexDeleteRule('/\d+/');
            $dagger->includeCode('');
            $this->assertSame(' is a number.', $codeRepository->includeCodeInput);
        }

        public function testAddReplaceRule(): void
        {
            $codeRepository = new PerceiveCodeRepository();
            $dagger = new Dagger($codeRepository);
            $dagger->addReplaceRule('not found string', 'nothing');
            $dagger->addReplaceRule('is a number', ': Answer to the Ultimate Question of Everything');
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

        public function testAddInsertBeforeRule(): void
        {
            $codeRepository = new PerceiveCodeRepository();
            $dagger = new Dagger($codeRepository);
            $dagger->addInsertBeforeRule('not found string', 'nothing');
            $dagger->addInsertBeforeRule('number', 'answer and ');
            $dagger->includeCode('');
            $this->assertSame('42 is a answer and number.', $codeRepository->includeCodeInput);
        }

        public function testAddRegexInsertBeforeRule(): void
        {
            $codeRepository = new PerceiveCodeRepository();
            $dagger = new Dagger($codeRepository);
            $dagger->addRegexInsertBeforeRule('/not found string/', 'nothing');
            $dagger->addRegexInsertBeforeRule('/\d+/', '(Number) ');
            $dagger->includeCode('');
            $this->assertSame('(Number) 42 is a number.', $codeRepository->includeCodeInput);
        }

        public function testAddInsertAfterRule(): void
        {
            $codeRepository = new PerceiveCodeRepository();
            $dagger = new Dagger($codeRepository);
            $dagger->addInsertAfterRule('not found string', 'nothing');
            $dagger->addInsertAfterRule('number', ' and answer');
            $dagger->includeCode('');
            $this->assertSame('42 is a number and answer.', $codeRepository->includeCodeInput);
        }

        public function testAddRegexInsertAfterRule(): void
        {
            $codeRepository = new PerceiveCodeRepository();
            $dagger = new Dagger($codeRepository);
            $dagger->addRegexInsertAfterRule('/not found string/', 'nothing');
            $dagger->addRegexInsertAfterRule('/\d+/', ' (Number)');
            $dagger->includeCode('');
            $this->assertSame('42 (Number) is a number.', $codeRepository->includeCodeInput);
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

        public function testRemoveAllRules(): void
        {
            $codeRepository = new PerceiveCodeRepository();
            $dagger = new Dagger($codeRepository);
            $dagger->addReplaceRule('is a number', ': Answer to the Ultimate Question of Everything');
            $dagger->removeAllRules();
            $dagger->includeCode('');
            $this->assertSame('42 is a number.', $codeRepository->includeCodeInput);
        }
    }
