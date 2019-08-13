---
layout: default
title: URI components
---

Uri Components
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-components)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-components.svg?style=flat-square)](https://github.com/thephpleague/uri-components/releases)

This package contains classes to help parsing and modifying URI components.

- Simple interface for building and parsing URI components;
- Interact with implementing PSR-7 `UriInterface` objects;

~~~php
use League\Uri\Components\Query;
use League\Uri\Uri;
use League\Uri\UriModifier;

$uri = Uri::createFromString('http://example.com?q=value#fragment');
$newUri = UriModifier::appendQuery($uri, 'q=new.Value');
echo $newUri; // 'http://example.com?q=value&q=new.Value#fragment';

$query = Query::createFromUri($newUri);
$newQuery->get('q');    // returns 'value'
$newQuery->getAll('q'); // returns ['value', 'new.Value']
$newQuery->params('q'); // returns 'new.Value'
~~~

System Requirements
-------

You need **PHP >= 7.2** but the latest stable version of PHP is recommended

In order to handle:

- IDN host you are required to also install the `intl` extension;
- IPv4 host in octal or hexadecimal form, out of the box, you are required to:
    - install the `GMP` extension **and/or**
    - install the `BCMath` extension **and/or**
    - use a `64-bits` PHP version

otherwise an exception will be thrown when attempting to validate or process such hosts.

Installation
--------

~~~
$ composer require league/uri-components
~~~

Dependencies
-------

- [League Uri Interfaces](https://github.com/thephpleague/uri-interfaces)
- [PSR-7](http://www.php-fig.org/psr/psr-7/)

What you will be able to do
--------

- Build and parse query with [QueryString](/components/2.0/querystring/)
- Partially modify URI with [URI Modifiers](/components/2.0/modifiers/)
- Create and Manipulate URI components objects with a [Common API](/components/2.0/api/)
