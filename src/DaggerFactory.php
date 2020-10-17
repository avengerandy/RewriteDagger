<?php declare(strict_types=1);

    namespace RewriteDagger;

    use RewriteDagger\CodeRepository\IncludeFileCodeRepository;

    class DaggerFactory
    {
        public function getDagger(): Dagger
        {
            $codeRepository = new IncludeFileCodeRepository();
            return $this->initDagger(new Dagger($codeRepository));
        }

        protected function initDagger(Dagger $dagger): Dagger
        {
            return $dagger;
        }
    }
