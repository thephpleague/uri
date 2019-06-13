---
layout: default
title: URI Query string parser
---

Parse & Build a Query
=======

The `League\Uri\Components\QueryString` is a userland PHP URI query parser and builder.

```php
<?php

use League\Uri\Components\QueryString;

$pairs = QueryString::parse('module=home&action=show&page=😓');
// returns [
//     ['module', 'home'],
//     ['action', 'show'],
//     ['page', '😓']
// ];

$str = QueryString::build($pairs, '|');
// returns 'module=home|action=show|page=😓'
```

Usage
--------

<p class="message-notice">The parsing/building algorithms preserve pairs order and uses the same algorithm used by JavaScript <a href="https://developer.mozilla.org/en-US/docs/Web/API/URLSearchParams/URLSearchParams">UrlSearchParams</a></p>

## Parsing the URI query string

Parsing a query string is easy.

```php
$pairs = QueryString::parse('module=home&action=show&page=😓');
// returns [
//     ['module', 'home'],
//     ['action', 'show'],
//     ['page', '😓']
// ];
```

### Description

```php
<?php

public static function QueryString::parse($query, string $separator = '&', int $enc_type = PHP_QUERY_RFC3986): array;
```

The returned array is a collection of key/value pairs. Each pair is represented as an array where the first element is the pair key and the second element the pair value. While the pair key is always a string, the pair value can be a string or the `null` value.

The `QueryString::parse` method parameters are:

- `$query` can be the `null` value, any scalar or object which is stringable;
- `$separator` is a string; by default it is the `&` character;
- `$enc_type` is one of PHP's constant `PHP_QUERY_RFC3968` or `PHP_QUERY_RFC1738` which represented the supported encoding algoritm
    - If you specify `PHP_QUERY_RFC3968` decoding will be done using [RFC3986](https://tools.ietf.org/html/rfc3986#section-3.4) rules;
    - If you specify `PHP_QUERY_RFC1738` decoding will be done using [application/x-www-form-urlencoded](https://url.spec.whatwg.org/#urlencoded-parsing) rules;

Here's a simple example showing how to use all the given parameters:

```php
<?php

$pairs = QueryString::parse(
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

<p class="message-warning">The <code>$separator</code> argument can not be the empty string.</p>

## Building the URI query string

To convert back the collection of key/value pairs into a valid query string or the `null` value you can use the static public `QueryString::build` method.

```php
<?php

$pairs = QueryString::build([
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

public static function QueryString::build(iterable $pairs, string $separator = '&', int $enc_type = PHP_QUERY_RFC3986): ?string;
```

The static public `QueryString::build` method parameters are:

- `$pairs` an iterable structure containing a collection of key/pair pairs as describe in the returned array of the `QueryString::parse` method.
- `$separator` is a string; by default it is the `&` character;
- `$enc_type` is one of PHP's constant `PHP_QUERY_RFC3968` or `PHP_QUERY_RFC1738` which represented the supported encoding algoritm
    - If you specify `PHP_QUERY_RFC3968` encoding will be done using [RFC3986](https://tools.ietf.org/html/rfc3986#section-3.4) rules;
    - If you specify `PHP_QUERY_RFC1738` encoding will be done using [application/x-www-form-urlencoded](https://url.spec.whatwg.org/#urlencoded-parsing) rules;

- the function returns the `null` value if the submitted collection is empty.

<p class="message-warning">The <code>$separator</code> argument can not be the empty string.</p>

## Extracting PHP variables

```php
<?php

public static function QueryString::extract($query, string $separator = '&', int $enc_type = PHP_QUERY_RFC3986): array;
public static function QueryString::convert(iterable $pairs): array;
```

`QueryString::parse` and `QueryString::build` preserve the query string pairs content and order. If you want to extract PHP variables from the query string *à la* `parse_str` you can use:

- The `QueryString::extract` method which takes the same parameters as `QueryString::parse`
- The `QueryString::convert` method which takes the result of `QueryString::parse`

both methods, however, do not allow parameters key mangling in the returned array like  `parse_str`;

```php
<?php

$query = 'module=show&arr.test[1]=sid&arr test[4][two]=fred&+module+=hide';

$params = QueryString::extract($query, '&', PHP_QUERY_RFC1738);
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

All exceptions extends the `League\Uri\Exceptions\UriException` marker class which extends PHP's `Throwable` class.

```php
<?php

use League\Uri\Exception\InvalidUriComponent;
use League\Uri\Parser\QueryString;

try {
    QueryString::extract('foo=bar', '&', 42);
} catch (UriException $e) {
    //$e is an instanceof League\Uri\Exceptions\SyntaxError
}
```
