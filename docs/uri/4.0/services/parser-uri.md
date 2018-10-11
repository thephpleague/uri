---
layout: default
title: The URI Parser
redirect_from:
    - /4.0/uri/services/parse-uri/
---

# The URI Parser

Because the library parses URIs in accordance to RFC3986, it ships with it own URI parser.

## Parsing an URI to extract its components

~~~php
<?php

public UriParser::parse(string $uri): string
public UriParser::__invoke(string $uri): string
~~~


<p class="message-notice">Since <code>version 4.1</code>: <code>UriParser::__invoke</code> is an alias of <code>UriParser::parse</code>.</p>


The methods parse an URI according to RFC3986 and expect a `string`. They return a hash representation of the URI similar to `parse_url` results.

Here are the main differences with PHP's `parse_url` function:

The `UriParser::parse` always returns all URI components.

~~~php
<?php

use League\Uri\UriParser;

$parser = new UriParser();
var_dump($parser->parse('http://www.example.com/'));
//returns the following array
//[
//    'scheme' => 'http',
//    'user' => null,
//    'pass' => null,
//    'host' => 'www.example.com',
//    'port' => null,
//    'path' => '/',
//    'query' => null,
//    'fragment' => null,
//];

var_dump(parse_url('http://www.example.com/'));
//returns the following array
//[
//    'scheme' => 'http',
//    'host' => 'www.example.com',
//    'path' => '/',
//];
~~~

Accessing individual component is simple without needing extra parameters:

~~~php
<?php

use League\Uri\UriParser;

$uri = 'http://www.example.com/';
$parser = new UriParser();
$parser->parse($uri)['query']; //returns null
$parser($uri)['query']; //returns null
parse_url($uri, PHP_URL_QUERY); //returns null
~~~

A distinction is made between an unspecified component, which will be set to `null` and an empty component which will be equal to the empty string.

~~~php
<?php

use League\Uri\UriParser;

$uri = 'http://www.example.com/?';
$parser = new UriParser();
$parser->parse($uri)['query'];  //returns ''
$parser($uri)['query'];         //returns ''
parse_url($uri, PHP_URL_QUERY); //returns `null`
~~~

Since a URI is made of at least a path component, this component is never equal to `null`

~~~php
<?php

use League\Uri\UriParser;

$uri = 'http://www.example.com?';
$parser = new UriParser();
$parser->parse($uri)['path'];  //returns ''
$parser($uri)['path'];         //returns ''
parse_url($uri, PHP_URL_PATH); //returns `null`
~~~

<p class="message-notice">The <code>UriParser</code> class only parse and extract from the URI string its components. You still need to validate them against its scheme specific rules.</p>

~~~php
<?php

use League\Uri\UriParser;

$uri = 'http:www.example.com';
$parser = new UriParser();
var_dump($parser($uri));
//returns the following array
//[
//    'scheme' => 'http',
//    'user' => null,
//    'pass' => null,
//    'host' => null,
//    'port' => null,
//    'path' => 'www.example.com',
//    'query' => null,
//    'fragment' => null,
//];
~~~

<p class="message-warning">This invalid HTTP URI is succefully parsed.</p>
