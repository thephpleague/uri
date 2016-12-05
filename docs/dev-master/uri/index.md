---
layout: default
title: Uri objects scheme specific
---

Uri Schemes
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri-schemes/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-schemes)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-schemes.svg?style=flat-square)](https://github.com/thephpleague/uri-components/schemes)

This package contains concrete URI objects represented as immutable value object. Each URI object implements the `League\Uri\Interfaces\Uri` interface as defined in the [uri-interfaces package](https://github.com/thephpleague/uri-interfaces) or the `Psr\Http\Message\UriInterface` from [PSR-7](http://www.php-fig.org/psr/psr-7/).


The following URI objects are defined (order alphabetically):

- [League\Uri\Schemes\Data](/dev-master/uri/schemes/data/) : represents a Data scheme URI
- [League\Uri\Schemes\File](/dev-master/uri/schemes/file/) : represents a File scheme URI
- [League\Uri\Schemes\FTP](/dev-master/uri/schemes/ftp/) : represents a FTP scheme URI
- [League\Uri\Schemes\Http](/dev-master/uri/schemes/http/) : represents a HTTP/HTTPS scheme URI
- [League\Uri\Schemes\Ws](/dev-master/uri/schemes/ws/) : represents a WS/WSS scheme URI


<p class="message-info">But you can easily <a href="/dev-master/uri/extension/">create your own class</a> to manage others scheme specific URI.</p>