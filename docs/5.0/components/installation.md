---
layout: default
title: URI components - installation
---

Installation
=======

System Requirements
-------

You need:

- **PHP >= 7.0** but the latest stable version of PHP is recommended
- the `intl` extension

Installation
--------

~~~
$ composer require league/uri-components
~~~

Dependencies
-------

Prior to version 1.4.0

- [PHP Domain Parser](https://github.com/jeremykendall/php-domain-parser)

Starting with version 1.4.0

- [Uri Hostname parser](/5.0/publicsuffix/)

The changes between dependencies was done to support `PHP7.2+`