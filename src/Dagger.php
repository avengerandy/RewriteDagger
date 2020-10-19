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

        public function addDeleteRule(String $from): void
        {
            $this->addReplaceRule($from, '');
        }

        public function addRegexDeleteRule(String $from): void
        {
            $this->addRegexReplaceRule($from, '');
        }

        public function addReplaceRule(String $from, String $to): void
        {
            $pattern = preg_quote($from, '/');
            $this->addRegexReplaceRule("/{$pattern}/", $to);
        }

        public function addRegexReplaceRule(String $from, String $to): void
        {
            $this->addRegexReplaceCallbackRule($from, function () use ($to) {
                return $to;
            });
        }

        public function addInsertBeforeRule(String $from, String $to): void
        {
            $pattern = preg_quote($from, '/');
            $this->addRegexInsertBeforeRule("/{$pattern}/", $to);
        }

        public function addRegexInsertBeforeRule(String $from, String $to): void
        {
            $this->addRegexReplaceCallbackRule($from, function ($match) use ($to) {
                return $to . $match[0];
            });
        }

        public function addInsertAfterRule(String $from, String $to): void
        {
            $pattern = preg_quote($from, '/');
            $this->addRegexInsertAfterRule("/{$pattern}/", $to);
        }

        public function addRegexInsertAfterRule(String $from, String $to): void
        {
            $this->addRegexReplaceCallbackRule($from, function ($match) use ($to) {
                return $match[0] . $to;
            });
        }

        public function addRegexReplaceCallbackRule(String $from, callable $callback): void
        {
            $this->ruleList[$from] = $callback;
        }

        public function removeAllRules(): void
        {
            $this->ruleList = [];
        }

        public function includeCode(String $path): void
        {
            $codeContent = $this->codeRepository->getCodeContent($path);
            // preg_replace_callback_array will return null when ruleList = []
            if (count($this->ruleList) > 0) {
                $codeContent = preg_replace_callback_array($this->ruleList, $codeContent);
            }
            // preg_replace_callback_array will return null when regex error
            if(is_null($codeContent)) {
                $errorCode = preg_last_error();
                throw new \RuntimeException("preg_replace preg_last_error: code {$errorCode}");
            }
            $this->codeRepository->includeCode($codeContent);
        }
    }
