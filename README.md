# RewriteDagger

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

## Dagger

## CodeRepository

### FileCodeRepository

### EvalCodeRepository

## DaggerFactory

# Related

# Testing

# Disadvantage

# License
