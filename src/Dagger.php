<?php declare(strict_types=1);

    namespace RewriteDagger;

    class Dagger
    {
        public function testFunction(int $var): int
        {
            return $var;
        }
    }
