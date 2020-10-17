<?php declare(strict_types=1);

    use PHPUnit\Framework\TestCase;

    use RewriteDagger\Dagger;
    use RewriteDagger\DaggerFactory;
    use RewriteDagger\CodeRepository\FileCodeRepository;
    use RewriteDagger\CodeRepository\IncludeFileCodeRepository;

    class PerceiveDaggerFactory extends DaggerFactory
    {
        protected function initDagger(Dagger $dagger): Dagger
        {
            $dagger->addReplaceRule('from', 'to');
            return $dagger;
        }
    }

    final class DaggerFactoryTest extends TestCase
    {
        public function testGetDagger(): void
        {
            $dagger = (new DaggerFactory)->getDagger();
            $this->assertInstanceOf(Dagger::class, $dagger);
            $codeRepository = $this->getDaggerPrivateProperty($dagger, 'codeRepository');
            $this->assertInstanceOf(FileCodeRepository::class, $codeRepository);
            $this->assertInstanceOf(IncludeFileCodeRepository::class, $codeRepository);
        }

        public function testInitDagger(): void
        {
            $daggerFactory = new PerceiveDaggerFactory();
            $dagger = $daggerFactory->getDagger();
            $ruleList = $this->getDaggerPrivateProperty($dagger, 'ruleList');
            $this->assertSame('to', $ruleList['/from/']());
        }

        private function getDaggerPrivateProperty(Dagger $dagger, string $propertyName)
        {
            $reflectionDagger = new \ReflectionObject($dagger);
            $property = $reflectionDagger->getProperty($propertyName);
            $property->setAccessible(true);
            return $property->getValue($dagger);
        }
    }
