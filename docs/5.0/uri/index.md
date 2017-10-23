---
layout: default
title: Uri objects scheme specific
---

Uri Schemes
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri-schemes/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-schemes)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-schemes.svg?style=flat-square)](https://github.com/thephpleague/uri-components/schemes)

This package contains concrete URI objects represented as immutable value object. Each URI object implements the `League\Uri\Interfaces\Uri` interface as defined in the [uri-interfaces package](https://github.com/thephpleague/uri-interfaces).

The following URI objects are defined (order alphabetically):

- [Data](/5.0/uri/schemes/data/) : represents a Data scheme URI
- [File](/5.0/uri/schemes/file/) : represents a File scheme URI
- [FTP](/5.0/uri/schemes/ftp/) : represents a FTP scheme URI
- [Http](/5.0/uri/schemes/http/) : represents a HTTP/HTTPS scheme URI, implements PSR-7 `UriInterface`
- [URI](/5.0/uri/schemes/uri/) : represents a generic RFC3986 URI object
- [Ws](/5.0/uri/schemes/ws/) : represents a WS/WSS scheme URI


<p class="message-info">But you can easily <a href="/5.0/uri/extension/">create your own class</a> to manage others scheme specific URI.</p>

To ease URI objects creation a [Factory](/5.0/uri/factory) is introduced as well as a [create](/5.0/uri/functions) function.