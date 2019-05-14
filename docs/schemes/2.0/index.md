---
layout: default
title: Uri Objects
---

Uri Schemes
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri-schemes/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-schemes)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-schemes.svg?style=flat-square)](https://github.com/thephpleague/uri-components/schemes)

This package contains concrete objects to ease manipulating URI objects represented as immutable value object. 

The following URI objects are defined (order alphabetically):

- [Http](/schemes/2.0/psr7/) : represents an URI object implementing PSR-7 `UriInterface`
- [URI](/schemes/2.0/rfc3986/) : represents a generic RFC3986 URI object

To ease URI objects creation a manipulation, the following helper classes are added  (order alphabetically):

- the [UriInfo](/schemes/2.0/info) : retrieves RFC3986 related info from an URI object;
- the [UriResolver](/schemes/2.0/resolver) : resolves or relativizes an URI against a base URI;
- the [UriString](/schemes/2.0/parser-builder) : parses or builds an URI string into or from its components;

System Requirements
-------

You need **PHP >= 7.2** but the latest stable version of PHP is recommended

While the library no longer requires out of the box the `intl` extension, you should still require it if you are dealing with URIs containing non-ASCII host. Without it, URI creation or manipulation action will throw an exception if such hosts are used.

Installation
--------

~~~
$ composer require league/uri-schemes
~~~

Dependencies
-------

- [PSR-7](http://www.php-fig.org/psr/psr-7/)
- [League Uri Interfaces](https://github.com/thephpleague/uri-interfaces)
