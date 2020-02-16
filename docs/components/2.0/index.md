---
layout: default
title: URI components
redirect_from:
    - /components/
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
$query->get('q');    // returns 'value'
$query->getAll('q'); // returns ['value', 'new.Value']
$query->params('q'); // returns 'new.Value'
~~~

System Requirements
-------

You need **PHP >= 7.2** but the latest stable version of PHP is recommended

If you want to handle:

- IDN host you are **required** to install the `intl` extension;
- IPv4 host in octal or hexadecimal form, out of the box, you **need** at least one of the following extension:

    - install the `GMP` extension **or**
    - install the `BCMath` extension
    
   or you should be using
   
    - a `64-bits` PHP version

Trying to process such hosts without meeting those minimal requirements will trigger a `RuntimeException`.
- Data URI creation from a filepath, Since version `2.2.0`, the `fileinfo` extension is **required**.

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

- Build and parse query with [QueryString](/components/2.0/query-parser-builder/)
- Partially modify URI with [URI Modifiers](/components/2.0/modifiers/)
- Create and Manipulate URI components objects with a [Common API](/components/2.0/api/)
