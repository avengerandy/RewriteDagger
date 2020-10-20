<?php declare(strict_types=1);

    use PHPUnit\Framework\TestCase;

    use RewriteDagger\Dagger;
    use RewriteDagger\DaggerFactory;
    use RewriteDagger\CodeRepository\CodeRepositoryInterface;
    use RewriteDagger\CodeRepository\IncludeFileCodeRepository;
    use RewriteDagger\CodeRepository\RequireFileCodeRepository;
    use RewriteDagger\CodeRepository\EvalCodeRepository;

    // fake CodeRepository that can perceive initDagger operation
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
            // default
            $dagger = (new DaggerFactory)->getDagger();
            $this->assertInstanceOf(Dagger::class, $dagger);
            $codeRepository = $this->getDaggerPrivateProperty($dagger, 'codeRepository');
            $this->assertInstanceOf(CodeRepositoryInterface::class, $codeRepository);
            $this->assertInstanceOf(IncludeFileCodeRepository::class, $codeRepository);
            $this->assertSame(sys_get_temp_dir(), $codeRepository->getTempPath());

            // include
            $dagger = (new DaggerFactory)->getDagger([
                'codeRepositoryType' => 'include',
                'tempPath' => 'fake temp path'
            ]);
            $codeRepository = $this->getDaggerPrivateProperty($dagger, 'codeRepository');
            $this->assertInstanceOf(IncludeFileCodeRepository::class, $codeRepository);
            $this->assertSame('fake temp path', $codeRepository->getTempPath());

            // require
            $dagger = (new DaggerFactory)->getDagger([
                'codeRepositoryType' => 'require',
                'tempPath' => 'fake temp path'
            ]);
            $codeRepository = $this->getDaggerPrivateProperty($dagger, 'codeRepository');
            $this->assertInstanceOf(RequireFileCodeRepository::class, $codeRepository);
            $this->assertSame('fake temp path', $codeRepository->getTempPath());

            // eval
            $dagger = (new DaggerFactory)->getDagger([
                'codeRepositoryType' => 'eval'
            ]);
            $codeRepository = $this->getDaggerPrivateProperty($dagger, 'codeRepository');
            $this->assertInstanceOf(EvalCodeRepository::class, $codeRepository);
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
