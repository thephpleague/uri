---
layout: default
title: RFC3986 - RFC3987 Parser
redirect_from:
    - /5.0/parser/
    - /parser/
---

URI Parser
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri-parser/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-parser)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-parser.svg?style=flat-square)](https://github.com/thephpleague/uri-parser/releases)

This package contains

- a userland PHP uri parser compliant with [RFC 3986](http://tools.ietf.org/html/rfc3986) and [RFC 3987](http://tools.ietf.org/html/rfc3987) to replace PHP's `parse_url` function.

- helper functions to ease parsing and building URI.

## System Requirements

You need:

- **PHP >= 7.0** but the latest stable version of PHP is recommended

While the library no longer requires out of the box the `intl` extension starting with version `1.4.0` to work, you still require it if you are dealing with URIs containing non-ASCII host. Without it, the parser will throw an exception if such URI is parsed.

## Installation


~~~bash
$ composer require league/uri-parser
~~~