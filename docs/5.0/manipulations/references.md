---
layout: default
title: URI references
---

URI references
=======

~~~php
<?php

use League\Uri;

function Uri\is_absolute(mixed $uri): bool
function Uri\is_network_path(mixed $uri): bool
function Uri\is_absolute_path(mixed $uri): bool
function Uri\is_relative_path(mixed $uri): bool
function Uri\is_same_document(mixed $uri, mixed $base_uri): bool
function Uri\Modifiers\uri_reference(mixed $uri [, mixed $base_uri]): array
~~~

## is_absolute

<p class="message-info">available since version <code>1.1.0</code></p>

The `is_absolute` function tells whether the given URI object represents an absolute URI.

~~~php
<?php

use League\Uri;

Uri\is_absolute(Uri\Http::createFromServer($_SERVER)); //returns true
Uri\is_absolute(Uri\Http::createFromString("/🍣🍺")); //returns false
~~~

## is_absolute_path

<p class="message-info">available since version <code>1.1.0</code></p>

The `is_absolute_path` function tells whether the given URI object represents an absolute URI path.

~~~php
<?php

use League\Uri;

Uri\is_absolute_path(Uri\Http::createFromServer($_SERVER)); //returns false
Uri\is_absolute_path(Uri\Http::createFromString("/🍣🍺")); //returns true
~~~

## is_network_path

<p class="message-info">available since version <code>1.1.0</code></p>

The `is_network_path` function tells whether the given URI object represents an network path URI.

~~~php
<?php

use League\Uri;

Uri\is_network_path(Uri\Http::createFromString("//example.com/toto")); //returns true
Uri\is_network_path(Uri\Http::createFromString("/🍣🍺")); //returns false
~~~

## is_relative_path

<p class="message-info">available since version <code>1.1.0</code></p>

The `is_relative_path` function tells whether the given URI object represents a relative path.

~~~php
<?php

use League\Uri;

Uri\is_relative_path(Uri\Http::createFromString("🏳️‍🌈")); //returns true
Uri\is_relative_path(Uri\Http::createFromString("/🍣🍺")); //returns false
~~~

## is_same_document

<p class="message-info">available since version <code>1.1.0</code></p>

The `is_same_document` function tells whether the given URI object represents the same document.

~~~php
<?php

use League\Uri;

Uri\is_same_document(
    Uri\Http::createFromString("example.com?foo=bar#🏳️‍🌈"),
    Uri\Http::createFromString("exAMpLE.com?foo=bar#🍣🍺")
); //returns true
~~~

## uri_reference

<p class="message-warning">this function is deprecated as of version <code>1.1.0</code> and will be remove in the next major release.</p>

This function analyzes the submitted URI object and returns an associative array containing information regarding the URI-reference as per [RFC3986](https://tools.ietf.org/html/rfc3986#section-4.1).

### Parameters

- `$uri` implements `Psr\Http\Message\UriInterface` or `League\Uri\Interfaces\Uri`
- `$base_uri` optional, implements `Psr\Http\Message\UriInterface` or `League\Uri\Interfaces\Uri`. Required if you want to detect same document reference.

### Returns Values

An associative array is returned. The following keys are always present within the array and their content is always a boolean:

- `absolute_uri`
- `network_path`
- `absolute_path`
- `relative_path`
- `same_document`

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
