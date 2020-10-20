<?php declare(strict_types=1);

    namespace RewriteDagger;

    use RewriteDagger\CodeRepository\CodeRepositoryInterface;
    use RewriteDagger\CodeRepository\IncludeFileCodeRepository;
    use RewriteDagger\CodeRepository\RequireFileCodeRepository;
    use RewriteDagger\CodeRepository\EvalCodeRepository;

    class DaggerFactory
    {
        public function getDagger(array $config = []): Dagger
        {
            $codeRepository = $this->getCodeRepository($config);
            return $this->initDagger(new Dagger($codeRepository));
        }

        private function getCodeRepository(array $config): CodeRepositoryInterface
        {
            $tempPath = $config['tempPath'] ?? sys_get_temp_dir();
            $codeRepositoryType = $config['codeRepositoryType'] ?? 'include';

            $codeRepository = null;
            switch ($codeRepositoryType) {
                case 'include':
                    $codeRepository = new IncludeFileCodeRepository($tempPath);
                    break;
                case 'require':
                    $codeRepository = new RequireFileCodeRepository($tempPath);
                    break;
                case 'eval':
                    $codeRepository = new EvalCodeRepository();
                    break;
                default:
                    throw new \InvalidArgumentException("unknown codeRepositoryType: {$codeRepositoryType}");
                    break;
            }
            return $codeRepository;
        }

        protected function initDagger(Dagger $dagger): Dagger
        {
            return $dagger;
        }
    }
