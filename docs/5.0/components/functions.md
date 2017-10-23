---
layout: default
title: URI components functions
---

# URI components functions

## parse_query


This functions is an improved version of PHP's `parse_str` function. It is a alias of `Query::extract`

~~~php
<?php

function parse_query(string $query[, string $separator = '&' [, int $enc_type = ComponentInterface::RFC3986_ENCODING]]): array
~~~

This method deserializes the query string parameters into an associative `array` similar to PHP's `parse_str` when used with its optional second argument. This function expects the following arguments:

- The query string **required**;
- The query string separator **optional**, by default it is set to `&`;
- The query string encoding. One of the `ComponentInterface` encoding type constant, by default it is set to `ComponentInterface::RFC3986_ENCODING`.

The main differences with `parse_str` usage are the following:

- `parse_query` accepts parameters which describe the query string separator and encoding status.
- `parse_query` does not mangle the query string data.
- `parse_query` is not affected by PHP `max_input_vars`.

~~~php
<?php

use League\Uri\Components\Query;

$query_string = 'foo.bar=bar&foo_bar=baz';
parse_str($query_string, $out);
var_export($out);
// $out = ["foo_bar" => 'baz'];

$arr = parse_query($query_string);
// $arr = ['foo.bar' => 'bar', 'foo_bar' => baz'];
~~~
