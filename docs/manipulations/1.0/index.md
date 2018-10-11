---
layout: default
title: URI formatter and URI middleware
redirect_from:
    - /manipulations/
    - /5.0/manipulations/
    - /5.0/manipulations/installation/
---

URI manipulations
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri-manipulations/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-manipulations)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-manipulations.svg?style=flat-square)](https://github.com/thephpleague/uri-manipulations/releases)

The `League Uri Manipulations` repository contains:

- an URI formatter to format URI string representation output;
- URI middlewares to filter Uri objects;

To be used, the URI objects are required to implement one of the following interface:

- `League\Uri\Interfaces\Uri`;
- `Psr\Http\Message\UriInteface`;

All functions and classes are located under the following namespace : `League\Uri\Modifiers`

System Requirements
-------

You need:

- **PHP >= 7.0**  but the latest stable version of PHP is recommended

Installation
--------

~~~
$ composer require league/uri-manipulations
~~~

Dependencies
-------

- [PSR-7 UriInterface](http://php-fig.org/psr/psr-7/)
- [League URI Interfaces](https://github.com/thephpleague/uri-interfaces)
- [League URI Components](/5.0/components/)