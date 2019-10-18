---
layout: default
title: Uri
redirect_from:
    - /5.0/
---

# Overview

[![Author](//img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square)](https://twitter.com/nyamsprod)
[![Source Code](//img.shields.io/badge/source-league/uri-blue.svg?style=flat-square)](https://github.com/thephpleague/uri)
[![Latest Stable Version](//img.shields.io/github/release/thephpleague/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)
[![Software License](//img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](//img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri)
[![Total Downloads](//img.shields.io/packagist/dt/league/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)

The library is a **meta package** which provides simple and intuitive classes to parse, validate and manipulate URIs and their components in PHP.

<p class="message-warning">We no longer recommend installing this package directly.</p>

## System Requirements

* **PHP >= 7.0.13** but the latest stable version of PHP is recommended;
* `mbstring` extension;
* `intl` extension;

## Install

The package is a metapackage that aggregates all components related to processing and manipulating URI in PHP; in most cases, you will want a subset, and these may be installed separately.

The following components are part of the metapackage:

- [League Uri Parser](/parser/1.0/)
- [League Uri Schemes](/schemes/1.0/)
- [League Uri Components](/components/1.0/)
- [League Uri Manipulations](/manipulations/1.0/)
- [League Uri Hostname Parser](/domain-parser/1.0/) *since version 5.2 in replacement of PHP Domain Parser version 3.0*

The primary use case for installing the entire suite is when upgrading from a version 4 release.

If you decide you still want to install the entire [suite]( https://packagist.org/packages/league/uri) use [Composer](https://getcomposer.org/). This can be done by running the following command on a composer installed box:

~~~bash
$ composer require league/uri
~~~

Most modern frameworks will include Composer out of the box, but ensure the following file is included:

~~~php
<?php

// Include the Composer autoloader
require 'vendor/autoload.php';
~~~

