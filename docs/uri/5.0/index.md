---
layout: default
title: Uri
redirect_from:
    - /5.0/
    - /uri/
---

# Overview

[![Author](//img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square)](https://twitter.com/nyamsprod)
[![Source Code](//img.shields.io/badge/source-league/uri-blue.svg?style=flat-square)](https://github.com/thephpleague/uri)
[![Latest Stable Version](//img.shields.io/github/release/thephpleague/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)
[![Software License](//img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](//img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri)
[![Total Downloads](//img.shields.io/packagist/dt/league/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)

The library is a **meta package** which provides simple and intuitive classes to parse, validate and manipulate URIs and their components in PHP. Out of the box the library validates the following URI specific schemes:

- HTTP/HTTPS;
- Websockets;
- FTP;
- Data URIs;
- File URIs;

and allow to easily manage others scheme specific URIs.

## System Requirements

* **PHP >= 7.0.13** but the latest stable version of PHP is recommended;
* `mbstring` extension;
* `intl` extension;

## Install

The library is available on [Packagist][] and should be installed using [Composer][]. This can be done by running the following command on a composer installed box:

~~~bash
$ composer require league/uri
~~~

Most modern frameworks will include Composer out of the box, but ensure the following file is included:

~~~php
<?php

// Include the Composer autoloader
require 'vendor/autoload.php';
~~~

## Packages

The URI meta package contains:

the following **fully** decoupled libraries:

- [League Uri Hostname Parser](/domain-parser/1.0/)
- [League Uri Parser](/parser/1.0/)

the following libraries:

- [League Uri Schemes](/schemes/1.0/)
- [League Uri Components](/components/1.0/)
- [League Uri Manipulations](/manipulations/1.0/)

[Packagist]: https://packagist.org/packages/league/uri
[Composer]: https://getcomposer.org/
[PSR-4]: https://php-fig.org/psr/psr-4/