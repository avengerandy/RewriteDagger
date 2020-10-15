<?php declare(strict_types=1);

    use PHPUnit\Framework\TestCase;

    use RewriteDagger\Dagger;
    use RewriteDagger\DaggerFactory;
    use RewriteDagger\CodeRepository\FileCodeRepository;

    final class DaggerFactoryTest extends TestCase
    {
        public function testGetDagger(): void
        {
            $dagger = DaggerFactory::getDagger();
            $this->assertInstanceOf(Dagger::class, $dagger);
            $reflectionDagger = new \ReflectionObject($dagger);
            $codeRepositoryProperty = $reflectionDagger->getProperty('codeRepository');
            $codeRepositoryProperty->setAccessible(true);
            $codeRepository = $codeRepositoryProperty->getValue($dagger);
            $this->assertInstanceOf(FileCodeRepository::class, $codeRepository);
        }
    }
