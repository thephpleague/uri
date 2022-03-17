---
layout: default
title: RFC3986 - RFC3987 Parser
---

URI parser and builder
=======

The `League\Uri\UriString` class is a userland PHP URI parser and builder compliant with [RFC 3986](http://tools.ietf.org/html/rfc3986) and [RFC 3987](http://tools.ietf.org/html/rfc3987) to replace PHP's `parse_url` function.

## URI parsing

### The parser is RFC3986/RFC3987 compliant

~~~php
<?php

use League\Uri\UriString;

var_export(UriString::parse('http://foo.com?@bar.com/'));
//returns the following array
//array(
//  'scheme' => 'http',
//  'user' => null,
//  'pass' => null,
//  'host' => 'foo.com',
//  'port' => null,
//  'path' => '',
//  'query' => '@bar.com/',
//  'fragment' => null,
//);

var_export(parse_url('http://foo.com?@bar.com/'));
//returns the following array
//array(
//  'scheme' => 'http',
//  'host' => 'bar.com',
//  'user' => 'foo.com?',
//  'path' => '/',
//);
// Depending on the PHP version
~~~

### The Parser returns all URI components.

~~~php
var_export(UriString::parse('http://www.example.com/'));
//returns the following array
//array(
//  'scheme' => 'http',
//  'user' => null,
//  'pass' => null,
//  'host' => 'www.example.com',
//  'port' => null,
//  'path' => '/',
//  'query' => null,
//  'fragment' => null,
//);

var_export(parse_url('http://www.example.com/'));
//returns the following array
//array(
//  'scheme' => 'http',
//  'host' => 'www.example.com',
//  'path' => '/',
//);
~~~

### No extra parameters needed

~~~php
$uri = 'http://www.example.com/';
UriString::parse($uri)['query']; //returns null
parse_url($uri, PHP_URL_QUERY); //returns null
~~~

### Empty component and undefined component are not treated the same

A distinction is made between an unspecified component, which will be set to `null` and an empty component which will be equal to the empty string.

~~~php
$uri = 'http://www.example.com/?';
UriString::parse($uri)['query']; //returns ''
parse_url($uri, PHP_URL_QUERY);  //returns null
~~~

### The path component is never equal to `null`

Since a URI is made of at least a path component, this component is never equal to `null`

~~~php
UriString::parse($uri)['path']; //returns ''
parse_url($uri, PHP_URL_PATH);  //returns null
~~~

### The parser throws exception instead of returning `false`.

~~~php
parse_url($uri); //returns false

UriString::parse('//example.com:toto');
//throw a League\Uri\Contracts\UriException
~~~

### The parser is not a validator

Just like `parse_url`, the `League\Uri\Parser` only parses and extracts from the URI string its components.

<p class="message-info">You still need to validate them against its scheme specific rules.</p>

~~~php
$uri = 'http:www.example.com';
var_export(UriString::parse($uri));
//returns the following array
//array(
//  'scheme' => 'http',
//  'user' => null,
//  'pass' => null,
//  'host' => null,
//  'port' => null,
//  'path' => 'www.example.com',
//  'query' => null,
//  'fragment' => null,
//);
~~~

<p class="message-warning">This invalid HTTP URI is successfully parsed.</p>

## URI Building

~~~php
UriString::build(array $components): string
~~~

You can rebuild a URI from its hash representation returned by the `UriString::parse` method or PHP's `parse_url` function using the `UriString::build` public static method.  

<p class="message-notice">If you supply your own hash you are responsible for providing valid encoded components without their URI delimiters.</p>

~~~php
$base_uri = 'http://hello:world@foo.com?@bar.com/';
$components = UriString::parse($base_uri);
//returns the following array
//array(
//  'scheme' => 'http',
//  'user' => 'hello',
//  'pass' => 'world',
//  'host' => 'foo.com',
//  'port' => null,
//  'path' => '',
//  'query' => '@bar.com/',
//  'fragment' => null,
//);

$uri = UriString::build($components);

echo $uri; //displays http://hello:world@foo.com?@bar.com/
~~~

The `build` method provides similar functionality to the `http_build_url()` function from v1.x of the [`pecl_http`](https://pecl.php.net/package/pecl_http) PECL extension.
