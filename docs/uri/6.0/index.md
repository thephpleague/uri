---
layout: default
title: Uri Objects
redirect_from:
    - /uri/
---

Overview
=======

[![Author](//img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square)](https://twitter.com/nyamsprod)
[![Source Code](//img.shields.io/badge/source-league/uri-blue.svg?style=flat-square)](https://github.com/thephpleague/uri)
[![Latest Stable Version](//img.shields.io/github/release/thephpleague/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)
[![Software License](//img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)<br>
[![Build](https://github.com/thephpleague/uri/workflows/build/badge.svg)](https://github.com/thephpleague/uri/actions?query=workflow%3A%22build%22)
[![Total Downloads](//img.shields.io/packagist/dt/league/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)

This package contains concrete objects to ease creating and manipulating URI objects represented as immutable value objects. 

The following URI objects are defined (order alphabetically):

- [Http](/uri/6.0/psr7/) : represents an URI object implementing PSR-7 `UriInterface`
- [URI](/uri/6.0/rfc3986/) : represents a generic RFC3986 URI object

To ease URI objects creation and manipulation, the following helper classes are added (order alphabetically):

- the [UriInfo](/uri/6.0/info) : retrieves RFC3986 related info from an URI object;
- the [UriResolver](/uri/6.0/resolver-relativizer) : resolves or relativizes an URI against a base URI;
- the [UriString](/uri/6.0/parser-builder) : parses or builds an URI string into or from its components;
- the [UriTemplate](/uri/6.0/uri-template) : expands an URI template string into an URI object;

System Requirements
-------

You need **PHP >= 7.2** but the latest stable version of PHP is recommended.

In order to handle IDN host you are required to also install the `intl` extension otherwise an exception will be thrown when attempting to validate such host.

In order to create Data URI from a filepath, since version `6.2`, you are required to also install the `fileinfo` extension otherwise an exception will be thrown.

Installation
--------

~~~
$ composer require league/uri:^6.0
~~~

Dependencies
-------

- [League Uri Interfaces](https://github.com/thephpleague/uri-interfaces)
- [PSR-7](http://www.php-fig.org/psr/psr-7/)
