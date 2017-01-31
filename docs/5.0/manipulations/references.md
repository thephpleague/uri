---
layout: default
title: URI references
---

URI references
=======


~~~php
<?php

function League\Uri\Modifiers\uri_reference(mixed $uri [, mixed $base_uri]): array
~~~

This function analyzes the submitted URI object and returns an associative array containing information regarding the URI-reference as per [RFC3986](https://tools.ietf.org/html/rfc3986#section-4.1).

## Parameters

- `$uri` implements `Psr\Http\Message\UriInterface` or `League\Uri\Interfaces\Uri`
- `$base_uri` optional, implements `Psr\Http\Message\UriInterface` or `League\Uri\Interfaces\Uri`. Required if you want to detect same document reference.

## Returns Values

An associative array is returned. The following keys are always present within the array and their content is always a boolean:

- `absolute_uri`
- `network_path`
- `absolute_path`
- `relative_path`
- `same_document`

## Example

~~~php
<?php

use GuzzleHttp\Psr7\Uri as GuzzleUri;
use League\Uri\Schemes\Http;
use function League\Uri\Modifiers\uri_reference;

$guzzle_uri = new GuzzleUri("//스타벅스코리아.com/how/are/you?foo=baz");
$alt_uri = Http::createFromString("//xn--oy2b35ckwhba574atvuzkc.com/how/are/you?foo=baz#bar");

var_dump(uri_reference($guzzle_uri));
//displays
// array(5) {
//   'absolute_uri' => bool(false)
//   'network_path' => bool(true)
//   'absolute_path' => bool(false)
//   'relative_path' => bool(false)
//   'same_document' => bool(false)
// }

var_dump(uri_reference($guzzle_uri, $alt_uri));
//displays
// array(5) {
//   'absolute_uri' => bool(false)
//   'network_path' => bool(true)
//   'absolute_path' => bool(false)
//   'relative_path' => bool(false)
//   'same_document' => bool(true)  //can be true only if a base URI is provided
// }
~~~
