---
layout: default
title: URI string parser and builder
---

URI String parser and builder
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri-parser/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-parser)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-parser.svg?style=flat-square)](https://github.com/thephpleague/uri-parser/releases)

This package contains a userland PHP URI parser and builder compliant with [RFC 3986](http://tools.ietf.org/html/rfc3986), [RFC 3987](http://tools.ietf.org/html/rfc3987) and [RFC 6874](https://tools.ietf.org/html/rfc6874).

~~~php
<?php

use League\Uri\Parser\UriString;

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

~~~

System Requirements
-------

You need **PHP >= 7.1.3** but the latest stable version of PHP is recommended

<p class="message-warning">While the library no longer requires the <code>ext/intl</code> extension, it is strongly recommended to install this extension if you are dealing with URIs containing non-ASCII host.<br>Without the extension, the parser will throw a <code>InvalidURI</code> exception when trying to parse such URI.</p>


Installation
--------

~~~bash
$ composer require league/uri-parser
~~~

## URI Parsing

~~~php
<?php

public static function UriString::parse($uri): array
~~~

The `UriString::parse` static method is a drop-in replacement to PHP's `parse_url` function, with the following differences:

### The parser is RFC3986/RFC3987 compliant

~~~php
<?php

use League\Uri\Parser\UriString;

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
<?php

use League\Uri\Parser\UriString;

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
<?php

use League\Uri\Parser\UriString;

$uri = 'http://www.example.com/';

UriString::parse($uri)['query']; //returns null
parse_url($uri, PHP_URL_QUERY); //returns null
~~~

### Empty component and undefined component are not treated the same

A distinction is made between an unspecified component, which will be set to `null` and an empty component which will be equal to the empty string.

~~~php
<?php

use League\Uri\Parser\UriString;

$uri = 'http://www.example.com?';

UriString::parse($uri)['query'];  //returns ''
parse_url($uri, PHP_URL_QUERY); //returns null
~~~

### The path component is never equal to `null`

Since a URI is made of at least a path component, this component is never equal to `null`

~~~php
<?php

use League\Uri\Parser\UriString;

$uri = 'http://www.example.com?';
UriString::parse($uri)['path'];  //returns ''
parse_url($uri, PHP_URL_PATH); //returns null
~~~

### The parser throws exception instead of returning `false`.

~~~php
<?php

use League\Uri\Parser\UriString;

$uri = '//example.com:toto';
UriString::parse($uri);
//throw a League\Uri\Exception\InvalidURI

parse_url($uri); //returns false
~~~

### The parser is not a validator

Just like `parse_url`, `UriString::parse` only parses and extracts components from the URI string.

<p class="message-info">You still need to validate them against its scheme specific rules.</p>

~~~php
<?php

use League\Uri\Parser\UriString;

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

## URI Building

~~~php
<?php

public static function UriString::build(array $components): string
~~~

You can rebuild a URI from its hash representation returned by the `UriString::parse` method or PHP's `parse_url` function using the `UriString::build` public static method.  

<p class="message-warning">If you supply your own hash you are responsible for providing valid encoded components without their URI delimiters.</p>

~~~php
<?php

use League\Uri\Parser\UriString;

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