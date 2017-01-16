---
layout: default
title: RFC3986 - RFC3987 Parser
---

URI Parser API
=======

The parser exposes the following methods

~~~php
<?php

use League\Uri\Parser;

public Parser::__invoke(string $uri): array
public Parser::isHost(string $host): bool
~~~

## URI parsing

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

## Validating the URI host component

If you have a host **string** you can validate it against the parser as shown below:

~~~php
<?php

use League\Uri\Parser;

$parser = new Parser();
$parser->isHost('example.com'); //returns true
$parser->isHost('/path/to/yes'); //returns false
~~~

The string can be RFC3987 or RFC3986 compliant.