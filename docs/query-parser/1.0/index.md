---
layout: default
title: URI Query string parser
redirect_from:
    - /query-parser/
---

Uri Query Parser
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri-query-parser/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-query-parser)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-query-parser.svg?style=flat-square)](https://github.com/thephpleague/uri-query-parser/releases)

This package contains a userland PHP uri query parser and builder.

```php
<?php

use function League\Uri\query_parse;
use function League\Uri\query_build;

$pairs = query_parse('module=home&action=show&page=😓');
// returns [
//     ['module', 'home'],
//     ['action', 'show'],
//     ['page', '😓']
// ];

$str = query_build($pairs, '|');
// returns 'module=home|action=show|page=😓'
```

System Requirements
-------

You need:

- **PHP >= 7.0** but the latest stable version of PHP is recommended

Installation
--------

```bash
$ composer require league/uri-query-parser
```