---
layout: default
title: ICANN Section Public Suffix Resolver
redirect_from:
    - /domain-parser/
    - /5.0/publicsuffix/
---

URI Hostname Parser
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri-hostname-parser/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-hostname-parser)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-hostname-parser.svg?style=flat-square)](https://github.com/thephpleague/uri-hostname-parser/releases)

<p class="message-info">This library replaces <a href="https://github.com/jeremykendall/php-domain-parser/">PHP Domain Parser</a> starting with version <code>5.2.0</code></p>

This library contains

- a ICANN Section [Public Suffix List](https://publicsuffix.org/) Manager.
- a [Public Suffix Finder](/domain-parser/1.0/rules/) class to resolve domain names.
- a helper function to ease resolving domain names with default options.

This library contains a lightweight domain parser using the [Public Suffix List (PSL) ICANN section](http://publicsuffix.org/) based on the excellent [PHP Domain Parser](https://github.com/jeremykendall/php-domain-parser/) by [Jeremy Kendall](https://github.com/jeremykendall).

The main differences with [PHP Domain Parser](https://github.com/jeremykendall/php-domain-parser/) are:

- This library **only** uses the ICANN Section of the Public Suffix List data
- This library supports PHP7.2+
- This library does not validate the hostname nor the cookie header host part

To validate your hostname please refer to [URI components](https://github.com/thephpleague/uri-components/)  
To validate your cookie headers please use [PHP Domain Parser](https://github.com/jeremykendall/php-domain-parser/).

This library depends on [PSR-16](http://www.php-fig.org/psr/psr-16/).

System Requirements
-------

You require:

- **PHP >= 7.0** but the latest stable version of PHP is recommended
- the `intl` extension

Installation
--------

~~~bash
$ composer require league/uri-hostname-parser
~~~
