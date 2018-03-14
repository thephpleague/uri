---
layout: default
title: RFC3986 - RFC3987 Parser
---

Installation
=======

System Requirements
-------

You need:

- **PHP >= 7.0** but the latest stable version of PHP is recommended

While the library no longer requires out of the box the `intl` extension starting with version `1.4.0` to work, you still require it if you are dealing with URIs containing non-ASCII host. Without it, the parser will throw an exception if such URI is parsed.

Installation
--------

~~~bash
$ composer require league/uri-parser
~~~