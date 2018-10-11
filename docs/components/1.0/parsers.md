---
layout: default
title: The Query component
redirect_from:
    - /5.0/components/parsers/
---

Parsers
=======

<p class="message-info">Since <code>version 1.5.0</code></p>

The library provides the following classes to ease components parsing:

- `QueryParser` to parse and deserialize a query string.
- `QueryBuilder` to build a query string from a collection of key/value pairs.

<p class="message-notice">The parsers and their functions alias are defined in the <code>League\Uri</code> namespace.</p>

## QueryParser::extract

~~~php
<?php

public QueryParser::extract(string $query[, string $separator = '&' [, int $enc_type = PHP_QUERY_RFC3986]]): array
~~~

This method deserializes the query string parameters into an associative `array` similar to PHP's `parse_str` when used with its optional second argument. This static public method expects the following arguments:

- The query string **required**;
- The query string separator **optional**, by default it is set to `&`;
- The query string encoding. One of the `ComponentInterface` encoding type constant.

The main differences are with `parse_str` usage are the following:

- `QueryParser::extract` accepts a parameter which describes the query string separator
- `QueryParser::extract` does not mangle the query string data.
- `QueryParser::extract` is not affected by PHP `max_input_vars`.

~~~php
<?php

use League\Uri\QueryParser;

$query_string = 'foo.bar=bar&foo_bar=baz';
parse_str($query_string, $out);
var_export($out);
// $out = ["foo_bar" => 'baz'];

$parser = new QueryParser();
$arr =$parser->extract($query_string);
// $arr = ['foo.bar' => 'bar', 'foo_bar' => baz'];
~~~

<p class="message-info">Since version <code>1.2.0</code> The alias function <code>Uri\extract_query</code> is available</p>

~~~php
<?php

use League\Uri;

$query_string = 'foo.bar=bar&foo_bar=baz';
parse_str($query_string, $out);
var_export($out);
// $out = ["foo_bar" => 'baz'];

$arr = Uri\extract_query($query_string);
// $arr = ['foo.bar' => 'bar', 'foo_bar' => baz'];
~~~


## QueryParser::parse

~~~php
<?php

public QueryParser::parse(string $query[, string $separator = '&' [, int $enc_type = PHP_QUERY_RFC3986]]): array
~~~

This method parse the query string into an associative `array` of key/values pairs. The method expects three (3) arguments:

- The query string **required**;
- The query string separator **optional**, by default it is set to `&`;
- The query string encoding. One of the `ComponentInterface` encoding type constant.

The value returned for each pair can be:

- `null`,
- a `string`
- an array of `string` and/or `null` values.

~~~php
<?php

use League\Uri;

$parser = new QueryParser();

$query_string = 'toto.foo=bar&toto.foo=baz&foo&baz=';
$arr = $parser->parse($query_string, '&');
// [
//     "toto.foo" => ["bar", "baz"],
//     "foo" => null,
//     "baz" => "",
// ]
~~~


<p class="message-warning"><code>QueryParser::parse</code> is not simlar to <code>parse_str</code>, <code>QueryParser::extract</code> is.</p>

<p class="message-warning"><code>QueryParser::parse</code> and <code>QueryParser::extract</code> both convert the query string into an array but <code>QueryParser::parse</code> logic don't result in data loss.</p>

<p class="message-info">Since version <code>1.2.0</code> The alias function <code>Uri\parse_query</code> is available</p>

~~~php
<?php

use League\Uri;

$query_string = 'toto.foo=bar&toto.foo=baz&foo&baz=';
$arr = Uri\parse_query($query_string, '&');
// [
//     "toto.foo" => ["bar", "baz"],
//     "foo" => null,
//     "baz" => "",
// ]
~~~

## QueryParser::convert

<p class="message-info">Available since version <code>1.5.0</code>.</p>


~~~php
<?php

public QueryParser::convert(iterable $pairs): array

//function alias

function pairs_to_params(iterable $pairs): array
~~~

The `QueryParser::convert` deserializes a collection of query string key/value pairs into an associative array similar to PHP’s `parse_str` when used with its optional second argument. This method expects a single argument which represents the collection of key/value pairs as an iterable construct.

The main differences are with `parse_str` usage are the following:

- `QueryParser::extract` accepts a parameter which describes the query string separator
- `QueryParser::extract` does not mangle the query string data.
- `QueryParser::extract` is not affected by PHP `max_input_vars`.

You can also use the function alias which is `Uri\pairs_to_params`.

~~~php
<?php

use League\Uri;

$parser = new Uri\QueryParser();
$arr = $parser->convert(['foo.bar' => ['2', 3, true]]);
//or
$arr = Uri\pairs_to_params(['foo.bar' => ['2', 3, true]]);
//in both cases $arr = ['foo.bar' => 'true'];
~~~


## QueryBuilder::build

~~~php
<?php

public QueryBuilder::build(iterable $pairs[, string $separator = '&' [, int $enc_type = PHP_QUERY_RFC3986]]): array
~~~

The `QueryBuilder::build` restores the query string from the result of the `QueryBuilder::parse` method. The method expects at most 3 arguments:

- A valid `iterable` of key/value pairs to convert;
- The query string separator, by default it is set to `&`;
- The query string encoding using one of the `ComponentInterface` constant

<p class="message-info">By default the encoding is set to <code>EncodingInterface::RFC3986_ENCODING</code></p>
<p class="message-info">Since version <code>1.3.0</code> The method accepts any iterable construct.</p>

~~~php
<?php

use League\Uri\QueryBuilder;
use League\Uri\QueryParser;

$builder = new QueryBuilder();
$parser = new QueryParser();


$query_string = 'foo[]=bar&foo[]=baz';
$arr = $parser->parse($query_string, '&', Query::RFC3986_ENCODING);
// $arr include the following data ["foo[]" => ['bar', 'baz']];

$res = $builder->build($arr, '&', false);
// $res = 'foo[]=bar&foo[]=baz'
~~~

<p class="message-warning"><code>QueryBuilder::build</code> is not similar to <code>http_build_query</code>.</p>
<p class="message-info">Since version <code>1.2.0</code> The alias function <code>Uri\build_query</code> is available</p>
<p class="message-info">Since version <code>1.3.0</code> The function accepts any iterable construct.</p>

~~~php
<?php

use League\Uri;

$query_string = 'foo[]=bar&foo[]=baz';
$arr = Query::parse($query_string, '&', Query::RFC3986_ENCODING);
var_export($arr);
// $arr include the following data ["foo[]" => ['bar', 'baz']];

$res = Uri\build_query($arr, '&', false);
// $res = 'foo[]=bar&foo[]=baz'
~~~