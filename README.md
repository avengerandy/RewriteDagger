# RewriteDagger

<a href="https://github.com/avengerandy/RewriteDagger/actions?query=workflow%3Atests"><img src="https://github.com/avengerandy/RewriteDagger/workflows/tests/badge.svg" alt="tests"></a>
<a href="https://github.com/avengerandy/RewriteDagger/actions?query=workflow%3Acoding-style"><img src="https://github.com/avengerandy/RewriteDagger/workflows/coding-style/badge.svg" alt="coding-style"></a>

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
- [Related](#related)
- [License](#license)

# Install

# Features

RewriteDagger can mock **anything** that test target dependened. No matter those function and class is from PHP buildin, third party or your project.
Through rewrite test target code before it be included and evaluated, RewriteDagger can replace any words and content that exist in test target without any extension.

# Usage

## Quick start

there's a php function need to test follows three behavior are worked as expected
- header set
- output json format
- exit is called

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

This function is hard to test because it has a php builtin function which hard to perceive input (`header`) and a php language construct that will terminate execution of the script (`exit`).

To solve this problem, we test it by PHPUnit and RewriteDagger

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
            $dagger = (new DaggerFactory)->getDagger();
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

than we run phpunit

```
$ vendor/bin/phpunit ApiErrorResponseTest.php
PHPUnit 9.4.0 by Sebastian Bergmann and contributors.

Api Error Response
 ✔ Api error response

Time: 00:00.015, Memory: 6.00 MB

OK (1 test, 3 assertions)
```

Though RewriteDagger, we can easily replace `header` and `exit` to other class which can perceive input and not terminate execution of the script.

## How it works

As [Features](#features) say, RewriteDagger rewrite test target code before it be included and evaluated.

To achieve this function, it has three core part:
 - Dagger: rewrite test target code
 - CodeRepository: included and evaluated test target code
 - DaggerFactory: create Dagger

Dagger mainly focse on various rewrite rule itself, then operate, include and evaluate code with CodeRepository that injection by DaggerFactory.

Follows, we explain the use of these three components separately.

## Dagger

### `__construct(CodeRepositoryInterface $codeRepository)`

Dagger dependent on any implement of CodeRepositoryInterface to help it evaluate code that has been rewrited.

<br>

### `includeCode(String $path): void`

Include, rewrite, evaluate code file that corresponds to `$path`.

Dagger can has multi rewrite rules, when `includeCode` called, Dagger execution all of them on code file content before evaluate.

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

All codeRepository are implement `CodeRepositoryInterface` which provide

- `getCodeContent(string $path): string`: get code content that corresponds to `$path`.
- `includeCode(string $codeContent): void`: evaluate `$codeContent`.

In PHP, there are two way can evaluate a string as code. One is write string as a real file then `include` it, the other is use `eval` function. RewriteDagger implement them in `FileCodeRepository` and `EvalCodeRepository` respectively.

### FileCodeRepository

### EvalCodeRepository

## DaggerFactory

# Related

# Testing

All command about test are defined in `composer.json`.

The only thing you need to notice is that phpunit varsion in `composer.lock` is 9, which don't support php 7.2.
But RewriteDagger do support php 7.2, so make sure to run `compoesr update` to change phpunit varsion to 8 before test, if you are using php 7.2.

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

# License

MIT License
