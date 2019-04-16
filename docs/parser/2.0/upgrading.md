---
layout: default
title: Upgrading from 1.x to 2.x
---

# Upgrading from 1.x to 2.x

`2.0` is a new major version that comes with backward compatibility breaks.

This guide will help you migrate from a 3.x version to 4.0. It will only explain backward compatibility breaks, it will not present the new features ([read the documentation for that](/parser/2.0/)).

## Installation

If you are using composer then you should update the require section of your `composer.json` file.

~~~
composer require league/uri-parser:^2.0
~~~

This will edit (or create) your `composer.json` file.

## PHP version requirement

`2.0` requires a PHP version greater than or equal `7.1.3` (was previously `7.0.0`).

## Namespace and classname changes

To parse a URI string:

- in version 1.0 you used to instantiate `League\Uri\Parser`
- in version 2.0 you will need the `League\Uri\Parser\UriString` class.

Before:

~~~php
use League\Uri\Parser;

$parser = new Parser();
var_export($parser('http://foo.com?@bar.com/'));
var_export($parser->parse('http://foo.com?@bar.com/'));
~~~

After:

~~~php
use League\Uri\Parser\UriString;

var_export(UriString::parse('http://foo.com?@bar.com/'));
~~~

## Removed method and functions

All namespaced functions or method which where not parsing or building URI string are removed.

| removed methods    | remove functions alias  | possible replacement |
| ------------------ | ----------------------- | -------------------- |
| `Parser::isScheme` | `is_scheme`             | `UriString::parse`   |
| `Parser::isPort`   | `is_port`               | `UriString::parse`   |
| `Parser::isHost`   | `is_host`               | `UriString::parse`   |

Before:

~~~php
<?php

use League\Uri\Parser;
use function League\Uri\is_scheme;

$parser = new Parser();
$parser->isScheme('ssh+svn'); //returns true
$parser->isScheme('data:'); //returns false

is_scheme('ssh+svn'); //returns true
is_scheme('data:'); //returns false
~~~

After:

~~~php
use League\Uri\Parser\UriString;

$scheme = 'ssh+svn';
$parts = UriString::parse($possible_scheme.'://foo.com');
$scheme === $parts['scheme']; // returns true
~~~

## Replaced methods and/or functions

| remove functions  | replaced by        |
| ----------------- | ------------------ |
| `build`           | `UriString::build` |
| `parse`           | `UriString::parse` |

Before:

~~~php
<?php

use function League\Uri\build;
use function League\Uri\parse;

$base_uri = 'http://hello:world@foo.com?@bar.com/';
$uri = build(parse($base_uri));

echo $uri; //displays http://hello@foo.com?@bar.com/
~~~

After:

~~~php
<?php

use League\Uri\Parser\UriString;

$base_uri = 'http://hello:world@foo.com?@bar.com/';
$uri = UriString::build(UriString::parse($base_uri));

echo $uri; //displays http://hello:world@foo.com?@bar.com/
~~~

<p class="message-warning">The User info pass component is kept in version 2.0. It was removed from the resulting string in version 1.</p>