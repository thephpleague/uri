---
layout: default
title: URI components
redirect_from:
    - /5.0/components/
    - /5.0/components/installation/
---

Uri Components
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-components)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-components.svg?style=flat-square)](https://github.com/thephpleague/uri-components/releases)

This package contains concrete URI components object represented as immutable value object as well as function to ease component parsing.


List of URI component objects
--------

Each URI component object implements the `League\Uri\Components\ComponentInterface` interface.

All URI components objects are located under the following namespace : `League\Uri\Components`


The following URI component objects are defined (order alphabetically):

- [DataPath](/components/1.0/data-path/) : the Data Path component
- [HierarchicalPath](/components/1.0/hierarchical-path/) : the hierarchical Path component
- [Host](/components/1.0/host/) : the Host component
- [Fragment](/components/1.0/fragment/) : the Fragment component
- [Path](/components/1.0/path/) : the generic Path component
- [Port](/components/1.0/port/) : the Port component
- [Query](/components/1.0/query/) : the Query component
- [Scheme](/components/1.0/scheme/) : the Scheme component
- [UserInfo](/components/1.0/userinfo/) : the User Info component

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
