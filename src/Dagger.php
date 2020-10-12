<?php declare(strict_types=1);

    namespace RewriteDagger;

    use RewriteDagger\CodeRepository\CodeRepositoryInterface;

    class Dagger
    {
        private $codeRepository = null;
        private $ruleList = [];

        function __construct(CodeRepositoryInterface $codeRepository)
        {
            $this->codeRepository = $codeRepository;
        }

        public function addStringReplaceRule(String $from, String $to): void
        {
            $pattern = preg_quote($from, '/');
            $this->ruleList["/{$pattern}/"] = function () use ($to) {
                return $to;
            };
        }

        public function includeCode(String $path): void
        {
            $codeContent = preg_replace_callback_array(
                $this->ruleList,
                $this->codeRepository->getCodeContent($path)
            );
            if(is_null($codeContent)) {
                $errorCode = preg_last_error();
                $errorMessage = preg_last_error_msg();
                throw new \RuntimeException("preg_replace error code {$errorCode}: {$errorMessage}");
            }
            $this->codeRepository->includeCode($codeContent);
        }
    }
