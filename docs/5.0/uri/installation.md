---
layout: default
title: Uri objects scheme specific
---

Installation
=======

System Requirements
-------

You need:

- **PHP >= 7.0.13** but the latest stable version of PHP is recommended

While the library no longer requires out of the box the `intl` extension starting with version `1.2.0`, you should still require it if you are dealing with URIs containing non-ASCII host. Without it, URI creation or manipulation action will throw an exception if such hosts are used.

Installation
--------

~~~
$ composer require league/uri-schemes
~~~

Dependencies
-------

- [PSR-7](http://www.php-fig.org/psr/psr-7/)
- [League Uri Interfaces](https://github.com/thephpleague/uri-interfaces)
- [League Uri Parser](/5.0/parser/)