<?php declare(strict_types=1);

    namespace RewriteDagger;

    use RewriteDagger\CodeRepository\FileCodeRepository;

    class DaggerFactory
    {
        static public function getDagger()
        {
            $codeRepository = new FileCodeRepository();
            return new Dagger($codeRepository);
        }
    }
