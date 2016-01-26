---
layout: default
permalink: /
title: Uri
---

# Introduction

[![Author](http://img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square)](https://twitter.com/nyamsprod)
[![Source Code](http://img.shields.io/badge/source-league/uri-blue.svg?style=flat-square)](https://github.com/thephpleague/uri)
[![Latest Stable Version](https://img.shields.io/github/release/thephpleague/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)<br>
[![Build Status](https://img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri)
[![HHVM Status](https://img.shields.io/hhvm/league/uri.svg?style=flat-square)](http://hhvm.h4cc.de/package/league/uri)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/thephpleague/csv.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/uri/?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/uri.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/uri)
[![Total Downloads](https://img.shields.io/packagist/dt/league/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)

The library provides simple and intuitive classes to [instantiate](/uri/instantiation/) and [manipulate](/uri/manipulation/) URIs and their [components](/components/overview/) in PHP. Out of the box the library handles the following schemes:

- [HTTP/HTTPS](/uri/schemes/http/);
- [Websockets](/uri/schemes/ws/);
- [FTP](/uri/schemes/ftp/);
- [Data URIs](/uri/schemes/data-uri/);

and allow [to easily manage others scheme specific URIs](/uri/extension/).

The library ships with:

- a [RFC3986][] compliant parser for the [URI string](/services/parser-uri/);
- a parser for the [URI query string](/services/parser-query/) that preserves its content;
- a [URI formatter](/services/formatter/) to easily output URI strings;

Highlights
------

- Simple API
- [RFC3986][] compliant
- Implements the `UriInterface` from [PSR-7][]
- Fully documented
- Framework Agnostic
- Composer ready, [PSR-2][] and [PSR-4][] compliant

## Questions?

The package was created by Nyamagana Butera Ignace. Find him on Twitter at [@nyamsprod][].

[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/
[PSR-7]: http://www.php-fig.org/psr/psr-7/
[RFC3986]: http://tools.ietf.org/html/rfc3986
[@nyamsprod]: https://twitter.com/nyamsprod