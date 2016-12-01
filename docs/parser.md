Uri Parser
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri-parser/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-parser)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-parser.svg?style=flat-square)](https://github.com/thephpleague/uri-parser/releases)

This package contains a userland PHP uri parser compliant with [RFC 3986](http://tools.ietf.org/html/rfc3986).

System Requirements
-------

You need:

- **PHP >= 5.6.0** but the latest stable version of PHP is recommended
- the `intl` extension

Installation
--------

```bash
$ composer require league/uri-parser
```

Documentation
---------

This is a drop-in replacement to PHP's `parse_url` function, with the following differences:

- The parser is RFC3986 compliant

```php
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
```

- The `Parser::__invoke` method always returns all URI components.

```php
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
```

- Accessing individual component is simple without needing extra parameters:

```php
<?php

use League\Uri\Parser;

$uri = 'http://www.example.com/';
$parser = new Parser();
$parser($uri)['query']; //returns null
parse_url($uri, PHP_URL_QUERY); //returns null
```

- Empty component and undefined component are treated differently

A distinction is made between an unspecified component, which will be set to `null` and an empty component which will be equal to the empty string.

```php
<?php

use League\Uri\Parser;

$uri = 'http://www.example.com/?';
$parser = new Parser();
$parser($uri)['query'];         //returns ''
parse_url($uri, PHP_URL_QUERY); //returns null
```

- The path component is never equal to `null`

Since a URI is made of at least a path component, this component is never equal to `null`

```php
<?php

use League\Uri\Parser;

$uri = 'http://www.example.com?';
$parser = new Parser();
$parser($uri)['path'];         //returns ''
parse_url($uri, PHP_URL_PATH); //returns null
```

- On malformed URI, the parser will throw a `ParserException` exception which extends SPL `InvalidArgumentException` instead of returning `false`.

```php
<?php

use League\Uri\Parser;

$uri = '//user@:80';
$parser = new Parser();
$parser($uri);
//throw a ParserException

parse_url($uri); //returns false
```

- Just like `parse_url`, the `League\Uri\Parser` only parses and extracts from the URI string its components. **You still need to validate them against its scheme specific rules.**

```php
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
```

**This invalid HTTP URI is successfully parsed.**
