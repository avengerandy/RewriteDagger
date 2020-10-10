<?php declare(strict_types=1);

    use PHPUnit\Framework\TestCase;
    use RewriteDagger\Dagger;

    final class testTest extends TestCase
    {
        public function testCase1(): void
        {
            $dagger = new Dagger();
            $this->assertEquals(1, $dagger->testFunction(1));
        }
    }
