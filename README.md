# RewriteDagger

<a href="https://github.com/avengerandy/RewriteDagger/actions?query=workflow%3Atests"><img src="https://github.com/avengerandy/RewriteDagger/workflows/tests/badge.svg" alt="tests"></a>
<a href="https://github.com/avengerandy/RewriteDagger/actions?query=workflow%3Acoding-style"><img src="https://github.com/avengerandy/RewriteDagger/workflows/coding-style/badge.svg" alt="coding-style"></a>
<a href="https://packagist.org/packages/mazer/rewrite-dagger"><img src="https://img.shields.io/packagist/php-v/mazer/rewrite-dagger" alt="PHP Version Requires"></a>
<a href="https://packagist.org/packages/mazer/rewrite-dagger"><img src="https://img.shields.io/packagist/v/mazer/rewrite-dagger" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/mazer/rewrite-dagger"><img src="https://img.shields.io/packagist/l/mazer/rewrite-dagger" alt="License"></a>

A php test tool that mock **anything** without using any extensions.

# Table of content

- [Install](#install)
- [Features](#features)
- [Usage](#usage)
    - [Quick start](#quick-start)
    - [How it works](#how-it-works)
    - [Dagger](#dagger)
    - [CodeRepository](#codeRepository)
        - [FileCodeRepository](#fileCodeRepository)
        - [EvalCodeRepository](#evalCodeRepository)
    - [DaggerFactory](#daggerFactory)
- [Testing](#testing)
- [Disadvantage](#disadvantage)
- [Inspire](#inspire)
- [Related repo](#related-repo)
- [License](#license)

# Install

```
composer require mazer/rewrite-dagger
```

# Features

RewriteDagger can mock **anything** that test target dependened. No matter those function and class is from PHP buildin, third party or your project.
By rewriting the test target code before including and evaluating it, RewriteDagger can replace any words and content present in the test target without any extension.

# Usage

## Quick start

There is a PHP function that needs to be tested as follows

- header set
- output json
- exit

```php
<?php

    function apiErrorResponse(string $errorMsg): void
    {
        header('Content-type: application/json; charset=utf-8');
        echo(json_encode([
            'data' => [
                'error' => true,
                'message' => $errorMsg
            ]
        ]));
        exit();
    }
```

This function is difficult to test because it has a built-in PHP function (`header`) that is difficult to perceive input and a PHP language construct (`exit`) that terminates the script execution.

To solve this problem, we test it by PHPUnit and RewriteDagger.

```php
<?php declare(strict_types=1);

    use PHPUnit\Framework\TestCase;
    use RewriteDagger\DaggerFactory;

    // Mock class that can perceive and save function result
    class Mock
    {
        static $exitHasCalled = false;
        static $header = '';

        static public function exit(): void
        {
            self::$exitHasCalled = true;
        }

        static public function header(string $header): void
        {
            self::$header = $header;
        }
    }

    final class ApiErrorResponseTest extends TestCase
    {
        public function testApiErrorResponse(): void
        {
            $dagger = (new DaggerFactory())->getDagger();
            // add rewrite rule
            $dagger->addReplaceRule('exit', 'Mock::exit');
            $dagger->addReplaceRule('header', 'Mock::header');
            // include apiErrorResponse function
            $dagger->includeCode(__DIR__ . '/apiErrorResponse.php');

            // call test function
            ob_start();
            apiErrorResponse('test error message');
            $output = ob_get_clean();

            // assert expect and actual value
            $this->assertTrue(Mock::$exitHasCalled);
            $this->assertSame('Content-type: application/json; charset=utf-8', Mock::$header);
            $this->assertSame('{"data":{"error":true,"message":"test error message"}}', $output);
        }
    }
```

```
$ vendor/bin/phpunit ApiErrorResponseTest.php
PHPUnit 9.4.0 by Sebastian Bergmann and contributors.

Api Error Response
 ✔ Api error response

Time: 00:00.015, Memory: 6.00 MB

OK (1 test, 3 assertions)
```

With RewriteDagger, we can easily replace `header` and `exit` with other class that can perceive input and will not terminate script execution.

## How it works

As [Features](#features) say, RewriteDagger rewrite the test target code before include and evaluate it.

To achieve this function, it has three core parts:
 - Dagger: rewrite test target code
 - CodeRepository: include and evaluate test target code
 - DaggerFactory: create Dagger

Dagger mainly focuses on various rewriting rule itself, and uses the CodeRepository injected by DaggerFactory to operate, include and evaluate the code.

Next, we explain the usage of these three components separately.

## Dagger

### `__construct(CodeRepositoryInterface $codeRepository)`

Dagger dependents on any implementation of CodeRepositoryInterface to help it evaluate the rewritten code.

<br>

### `includeCode(String $path): void`

Include, rewrite, evaluate code file corresponding to `$path`.

Dagger can have multiple rewriting rules. When `includeCode` is called, Dagger executes all these rules on the code before evaluating it.

<br>

### `addDeleteRule(String $from): void`

```php
$dagger->addDeleteRule('is a number.');
```

|before|after|
|-|-|
|`42 is a number.`|`42 `|

<br>

### `testAddRegexDeleteRule(String $from): void`

```php
$dagger->addRegexDeleteRule('/\d+/');
```

|before|after|
|-|-|
|`42 is a number.`|` is a number.`|

<br>

### `addReplaceRule(String $from, String $to): void`

```php
$dagger->addReplaceRule('is a number', ': Answer to the Ultimate Question of Everything');
```

|before|after|
|-|-|
|`42 is a number.`|`42 : Answer to the Ultimate Question of Everything.`|

<br>

### `addRegexReplaceRule(String $from, String $to): void`

```php
$dagger->addRegexReplaceRule('/\d+/', 'Number');
```

|before|after|
|-|-|
|`42 is a number.`|`Number is a number.`|

<br>

### `addInsertBeforeRule(String $from, String $to): void`

```php
$dagger->addInsertBeforeRule('number', 'answer and ');
```

|before|after|
|-|-|
|`42 is a number.`|`42 is a answer and number.`|

<br>

### `addRegexInsertBeforeRule(String $from, String $to): void`

```php
$dagger->addRegexInsertBeforeRule('/\d+/', '(Number) ');
```

|before|after|
|-|-|
|`42 is a number.`|`(Number) 42 is a number.`|

<br>

### `addInsertAfterRule(String $from, String $to): void`

```php
$dagger->addInsertAfterRule('number', ' and answer');
```

|before|after|
|-|-|
|`42 is a number.`|`42 is a number and answer.`|

<br>

### `addRegexInsertAfterRule(String $from, String $to): void`

```php
$dagger->addRegexInsertAfterRule('/\d+/', ' (Number)');
```

|before|after|
|-|-|
|`42 is a number.`|`42 (Number) is a number.`|

<br>

### `addRegexReplaceCallbackRule(String $from, callable $callback): void`

```php
$dagger->addRegexReplaceCallbackRule('/^(\d+).*(number)\.$/', function ($match) {
    return "[{$match[1]}] is a ({$match[2]}).";
});
```

|before|after|
|-|-|
|`42 is a number.`|`[42] is a (number).`|

<br>

### `testRemoveAllRules(): void`

Remove all rules set before.

<br>

## CodeRepository

All codeRepository is the implementation of CodeRepositoryInterface which provide

- `getCodeContent(string $path): string`: get code content that corresponds to `$path`.
- `includeCode(string $codeContent): void`: evaluate `$codeContent`.

In PHP, there are two ways to evaluate a string as code. One is to write the string as a real file then `include()` or `require()` it, the other is to use `eval()` function. RewriteDagger implements them in FileCodeRepository and EvalCodeRepository respectively.

<br>

### FileCodeRepository

#### `__construct(string $tempPath = null)`

FileCodeRepository writes a string to a temporary file in `$tempPath` with a unique name, then includes and evaluates it. If `$tempPath` is `null`, FileCodeRepository automatically generated it by `sys_get_temp_dir()`.

- IncludeFileCodeRepository: use `include()` includes and evaluates the file.
- RequireFileCodeRepository: use `require()` includes and evaluates the file.

<br>

### EvalCodeRepository

#### `__construct()`

EvalCodeRepository is much simpler than FileCodeRepository, it `eval()` the input string directly.

<br>

## DaggerFactory

Generally, unless you want to use a custom CodeRepository in Dagger, Dagger and CodeRepository are usually created by DaggerFactory instead of manually.

### `getDagger(array $config = []): Dagger`

```php
<?php
    // default is use IncludeFileCodeRepository
    $dagger = (new DaggerFactory())->getDagger();

    // explicit use IncludeFileCodeRepository
    $dagger = (new DaggerFactory())->getDagger([
        'codeRepositoryType' => 'include',
        'tempPath' => 'your/temp/path/'
    ]);
```

|config key|description|
|-|-|
|codeRepositoryType|enum {include, require, eval}|
|tempPath|temp path for FileCodeRepository|

<br>

### `initDagger(Dagger $dagger): Dagger`

`initDagger` is a protected function that allows you to customize DaggerFactory, which can operate on Dagger before return it (mainly adding default rules).

```php
<?php
    class CustomDaggerFactory extends DaggerFactory
    {
        protected function initDagger(Dagger $dagger): Dagger
        {
            $dagger->addDeleteRule('exit()');
            return $dagger;
        }
    }

    // all dagger create by CustomDaggerFactory has a delete exit() rule by default
    $dagger = (new CustomDaggerFactory())->getDagger();
```

# Testing

All the commands for testing are defined in `composer.json`.

The only thing you need to notice is that phpunit varsion in `composer.lock` is 9, which don't support php 7.2.
But RewriteDagger does  support php 7.2, so if you are using php 7.2, make sure to run `compoesr update` to change phpunit varsion to 8 before testing.

```json
{
    // ...
    "scripts": {
        "test": "phpunit",
        "testWithCoverage": "phpunit --coverage-text --whitelist src/ --colors",
        "codingStyleCheck": "php-cs-fixer fix ./ --dry-run --diff"
    },
    // ...
}
```

test without coverage
```
composer test
```

test with coverage
```
composer testWithCoverage
```

check coding style
```
composer codingStyleCheck
```

# Disadvantage

The two biggest disadvantages of using RewriteDagger are reduced test coverage and readability.

- test coverage: Since the rewritten code does not belong to the original code in the project, for most test coverage tools, the original code in the project is not actually executed.
- readability: People who read the test program must understand all of the tested target to understand the side effects of each rewriting rule.

# Inspire

RewriteDagger is inspired by the book 《*Working Effectively with Legacy Code*》 ( ISBN 13: 978-0131177055). Hope to provide *Link Seams* in PHP to make legacy PHP code easier to test.

# Related repo

[badoo/soft-mocks](https://github.com/badoo/soft-mocks)

# License

MIT License
