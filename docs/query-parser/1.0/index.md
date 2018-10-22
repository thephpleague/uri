---
layout: default
title: URI Query string parser
redirect_from:
    - /query-parser/
---

Uri Query Parser
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri-query-parser/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-query-parser)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-query-parser.svg?style=flat-square)](https://github.com/thephpleague/uri-query-parser/releases)

This package contains a userland PHP uri query parser and builder.

```php
<?php

use function League\Uri\query_parse;
use function League\Uri\query_build;

$pairs = query_parse('module=home&action=show&page=😓');
// returns [
//     ['module', 'home'],
//     ['action', 'show'],
//     ['page', '😓']
// ];

$str = query_build($pairs, '|');
// returns 'module=home|action=show|page=😓'
```

System Requirements
-------

You need:

- **PHP >= 7.0** but the latest stable version of PHP is recommended

Installation
--------

```bash
$ composer require league/uri-query-parser
```

Usage
--------

<p class="message-notice">The parsing/building algorithms preserve pairs order and uses the same algorithm used by JavaScript <a href="https://developer.mozilla.org/en-US/docs/Web/API/URLSearchParams/URLSearchParams">UrlSearchParams</a></p>

## Parsing the URI query string

Parsing a query string is easy.

```php
<?php

use function League\Uri\query_parse;

$pairs = query_parse('module=home&action=show&page=😓');
// returns [
//     ['module', 'home'],
//     ['action', 'show'],
//     ['page', '😓']
// ];
```

### Description

```php
<?php

function League\Uri\query_parse($query, string $separator = '&', int $enc_type = PHP_QUERY_RFC3986): array;
```

The returned array is a collection of key/value pairs. Each pair is represented as an array where the first element is the pair key and the second element the pair value. While the pair key is always a string, the pair value can be a string or the `null` value.

The `League\Uri\query_parse` parameters are

- `$query` can be the `null` value, any scalar or object which is stringable;
- `$separator` is a string; by default it is the `&` character;
- `$enc_type` is one of PHP's constant `PHP_QUERY_RFC3968` or `PHP_QUERY_RFC1738` which represented the supported encoding algoritm
    - If you specify `PHP_QUERY_RFC3968` decoding will be done using [RFC3986](https://tools.ietf.org/html/rfc3986#section-3.4) rules;
    - If you specify `PHP_QUERY_RFC1738` decoding will be done using [application/x-www-form-urlencoded](https://url.spec.whatwg.org/#urlencoded-parsing) rules;

Here's a simple example showing how to use all the given parameters:

```php
<?php

use function League\Uri\query_parse;

$pairs = query_parse(
    'module=home:action=show:page=toto+bar&action=hide',
    ':',
    PHP_QUERY_RFC1738
);
// returns [
//     ['module', 'home'],
//     ['action', 'show'],
//     ['page', 'toto bar'],
//     ['action', 'hide'],
// ];
```

## Building the URI query string

To convert back the collection of key/value pairs into a valid query string or the `null` value you can use the `League\Uri\query_build` function.

```php
<?php

use function League\Uri\query_build;

$pairs = query_build([
    ['module', 'home'],
    ['action', 'show'],
    ['page', 'toto bar'],
    ['action', 'hide'],
], '|', PHP_QUERY_RFC3986);

// returns 'module=home|action=show|page=toto%20bar|action=hide';
```

### Description

```php
<?php

function League\Uri\query_build(iterable $pairs, string $separator = '&', int $enc_type = PHP_QUERY_RFC3986): ?string;
```

The `League\Uri\query_build` :

- accepts any iterable structure containing a collection of key/pair pairs as describe in the returned array of the `League\Uri\query_parse` function.

Just like with `League\Uri\query_parse`, you can specify the separator and the encoding algorithm to use.

- the function returns the `null` value if an empty array or collection is given as input.

## Extracting PHP variables

`League\Uri\query_parse` and `League\Uri\query_build` preserve the query string pairs content and order. If you want to extract PHP variables from the query string *à la* `parse_str` you can use `League\Uri\query_extract`. The function:

- takes the same parameters as `League\Uri\query_parse`
- does not allow parameters key mangling in the returned array;

```php
<?php

use function League\Uri\query_extract;

$query = 'module=show&arr.test[1]=sid&arr test[4][two]=fred&+module+=hide';

$params = query_extract($query, '&', PHP_QUERY_RFC1738);
// $params contains [
//     'module' = 'show',
//     'arr.test' => [
//         1 => 'sid',
//     ],
//     'arr test' => [
//         4 => [
//             'two' => 'fred',
//         ]
//     ],
//     ' module ' => 'hide',
// ];

parse_str($query, $variables);
// $variables contains [
//     'module' = 'show',
//     'arr_test' => [
//         1 => 'sid',
//         4 => [
//             'two' => 'fred',
//         ],
//     ],
//     'module_' = 'hide',
// ];
```

## Exceptions

All exceptions extends the `League\Uri\Parser\InvalidUriComponent` marker class which extends PHP's `InvalidArgumentException` class.

- If the query string is invalid a `League\Uri\Parser\InvalidQueryString` exception is thrown.
- If the query pair is invalid a `League\Uri\Parser\InvalidQueryPair` exception is thrown.
- If the encoding algorithm is unknown or invalid a `League\Uri\Parser\UnknownEncoding` exception is thrown.

```php
<?php

use League\Uri\Parser\InvalidUriComponent;
use function League\Uri\query_extract;

try {
    query_extract('foo=bar', '&', 42);
} catch (InvalidUriComponent $e) {
    //$e is an instanceof League\Uri\Parser\UnknownEncoding
}
```