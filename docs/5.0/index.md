---
layout: default
title: Uri
---

# URI

[![Author](http://img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square)](https://twitter.com/nyamsprod)
[![Source Code](http://img.shields.io/badge/source-league/uri-blue.svg?style=flat-square)](https://github.com/thephpleague/uri)
[![Latest Stable Version](https://img.shields.io/github/release/thephpleague/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri)
[![Total Downloads](https://img.shields.io/packagist/dt/league/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)

The library is a **meta package** which provides simple and intuitive classes to [parse](/5.0/parser/), [validate](/5.0/uri/) and [manipulate](/5.0/manipulations/) URIs and their [components](/5.0/components/) in PHP. Out of the box the library validate the following URI specific schemes:

- HTTP/HTTPS;
- Websockets;
- FTP;
- Data URIs;
- File URIs;

and allow to easily manage others scheme specific URIs.

The library ships with:

- a [RFC3986][] and [RFC3987][] compliant parser for the [URI string](/5.0/parser/);
- a [URI formatter](/5.0/manipulations/formatter/) to easily output [RFC3987][] URI strings;
- [URI middlewares](/5.0/manipulations/middlewares/) and functions to ease URI manipulations;

Highlights
------

- Simple API
- [RFC3986][] and [RFC3987][] compliant
- Implements the `UriInterface` from [PSR-7][]
- Fully documented
- Framework Agnostic

Questions?
------

The package was created by Nyamagana Butera Ignace. Find him on Twitter at [@nyamsprod][].

[PSR-7]: http://www.php-fig.org/psr/psr-7/
[RFC3986]: http://tools.ietf.org/html/rfc3986
[RFC3987]: http://tools.ietf.org/html/rfc3987
[@nyamsprod]: https://twitter.com/nyamsprod