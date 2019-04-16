---
layout: default
title: RFC3986 - RFC3987 Parser
redirect_from:
    - /5.0/parser/
    - /parser/
---

URI String parser and builder
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri-parser/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-parser)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-parser.svg?style=flat-square)](https://github.com/thephpleague/uri-parser/releases)

This package contains

- a userland PHP uri parser compliant with [RFC 3986](http://tools.ietf.org/html/rfc3986) and [RFC 3987](http://tools.ietf.org/html/rfc3987) to replace PHP's `parse_url` function.

- helper functions to ease parsing and building URI.

## System Requirements

You need:

- **PHP >= 7.0** but the latest stable version of PHP is recommended

While the library no longer requires out of the box the `intl` extension starting with version `1.4.0` to work, you still require it if you are dealing with URIs containing non-ASCII host. Without it, the parser will throw an exception if such URI is parsed.

## Installation


~~~bash
$ composer require league/uri-parser
~~~

## URI parsing

<p class="message-info">available since version <code>1.3.0</code> <code>Parser::parse</code> is an alias of <code>Parser::__invoke</code>.</p>

The `Parser::__invoke` method is a drop-in replacement to PHP's `parse_url` function, with the following differences:

### The parser is RFC3986/RFC3987 compliant

~~~php
<?php

use League\Uri\Parser;

$parser = new Parser();
var_export($parser('http://foo.com?@bar.com/'));
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

use League\Uri\Parser;

$parser = new Parser();
var_export($parser('http://www.example.com/'));
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

use League\Uri\Parser;

$uri = 'http://www.example.com/';
$parser = new Parser();
$parser($uri)['query']; //returns null
parse_url($uri, PHP_URL_QUERY); //returns null
~~~

### Empty component and undefined component are not treated the same

A distinction is made between an unspecified component, which will be set to `null` and an empty component which will be equal to the empty string.

~~~php
<?php

use League\Uri\Parser;

$uri = 'http://www.example.com/?';
$parser = new Parser();
$parser($uri)['query'];         //returns ''
parse_url($uri, PHP_URL_QUERY); //returns null
~~~

### The path component is never equal to `null`

Since a URI is made of at least a path component, this component is never equal to `null`

~~~php
<?php

use League\Uri\Parser;

$uri = 'http://www.example.com?';
$parser = new Parser();
$parser($uri)['path'];         //returns ''
parse_url($uri, PHP_URL_PATH); //returns null
~~~

### The parser throws exception instead of returning `false`.

~~~php
<?php

use League\Uri\Parser;

$uri = '//example.com:toto';
$parser = new Parser();
$parser($uri);
//throw a League\Uri\Exception

parse_url($uri); //returns false
~~~

### The parser is not a validator

Just like `parse_url`, the `League\Uri\Parser` only parses and extracts from the URI string its components.

<p class="message-info">You still need to validate them against its scheme specific rules.</p>

~~~php
<?php

use League\Uri\Parser;

$uri = 'http:www.example.com';
$parser = new Parser();
var_export($parser($uri));
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

### function alias

<p class="message-info">available since version <code>1.1.0</code></p>

The library also provides a function alias to `Parser::__invoke`, `Uri\parse`:

~~~php
<?php

use function League\Uri\parse;

$components = parse('http://foo.com?@bar.com/');
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
~~~

## URI Building

~~~php
<?php

use League\Uri;

function build(array $components): string
~~~

<p class="message-info"><code>Uri\build</code> is available since version <code>1.1.0</code></p>

You can rebuild a URI from its hash representation returned by the `Parser::__invoke` method or PHP's `parse_url` function using the helper function `Uri\build`.  

If you supply your own hash you are responsible for providing valid encoded components without their URI delimiters.

~~~php
<?php

use function League\Uri\build;
use function League\Uri\parse;

$base_uri = 'http://hello:world@foo.com?@bar.com/';
$components = parse($base_uri);
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

$uri = build($components);

echo $uri; //displays http://hello@foo.com?@bar.com/
~~~

The `Uri\build` function never output the `pass` component as suggested by [RFC3986](https://tools.ietf.org/html/rfc3986#section-7.5).

## Scheme validation

<p class="message-info">available since version <code>1.2.0</code></p>

If you have a scheme **string** you can validate it against the parser. The scheme is considered to be valid if it is:

- an empty string;
- a string which follow [RFC3986 rules](https://tools.ietf.org/html/rfc3986#section-3.1);

~~~php
<?php

use League\Uri\Parser;

$parser = new Parser();
$parser->isScheme('example.com'); //returns false
$parser->isScheme('ssh+svn'); //returns true
$parser->isScheme('data');  //returns true
$parser->isScheme('data:'); //returns false
~~~

The library also provides a function alias `Uri\is_scheme`:

~~~php
<?php

use function League\Uri\is_scheme;

is_scheme('example.com'); //returns false
is_scheme('ssh+svn'); //returns true
is_scheme('data');  //returns true
is_scheme('data:'); //returns false
~~~

## Host validation

If you have a host **string** you can validate it against the parser. The host is considered to be valid if it is:

- an empty string;
- a IPv4;
- a formatted IPv6 (with or without its zone identifier);
- a registered name;

A registered name is a [domain name](http://tools.ietf.org/html/rfc1034) subset according to [RFC1123](http://tools.ietf.org/html/rfc1123#section-2.1). As such a registered name can not, for example, contain an `_`. The registered name can be RFC3987 or RFC3986 compliant.

~~~php
<?php

use League\Uri\Parser;

$parser = new Parser();
$parser->isHost('example.com'); //returns true
$parser->isHost('/path/to/yes'); //returns false
$parser->isHost('[:]'); //returns true
$parser->isHost('[127.0.0.1]'); //returns false
~~~

The library also provides a function alias `Uri\is_host`:

~~~php
<?php

use function League\Uri\is_host;

is_host('example.com'); //returns true
is_host('/path/to/yes'); //returns false
is_host('[:]'); //returns true
is_host('[127.0.0.1]'); //returns false
~~~

## Port validation

<p class="message-info">available since version <code>1.2.0</code></p>

If you have a port, you can validate it against the parser. The port is considered to be valid if it is:

- a numeric value which follow [RFC3986 rules](https://tools.ietf.org/html/rfc3986#section-3.2.3);

~~~php
<?php

use League\Uri\Parser;

$parser = new Parser();
$parser->isPort('example.com'); //returns false
$parser->isPort(888);           //returns true
$parser->isPort('23');    //returns true
$parser->isPort('data:'); //returns false
~~~

The library also provides a function alias `Uri\is_port`:

~~~php
<?php

use function League\Uri\is_port;

is_port('example.com'); //returns false
is_port(888);           //returns true
is_port('23');    //returns true
is_port('data:'); //returns false
~~~
