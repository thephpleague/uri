---
layout: default
title: URI components
---

Uri Components
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-components)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-components.svg?style=flat-square)](https://github.com/thephpleague/uri-components/releases)

This package contains classes to help parsing and modifying URI components.

List of URI component objects
--------

All URI components objects are located under the following namespace : `League\Uri\Components`

The following URI related classes are defined (order alphabetically):

- [Authority](/components/2.0/authority/) : the URI Authority part
- [DataPath](/components/2.0/path/data/) : the Data Path component
- [Domain](/components/2.0/host/domain/) : the Host component
- [Fragment](/components/2.0/fragment/) : the Fragment component
- [HierarchicalPath](/components/2.0/path/segmented/) : the Segmented Path component
- [Host](/components/2.0/host/) : the generic Host component
- [Path](/components/2.0/path/) : the generic Path component
- [Port](/components/2.0/port/) : the Port component
- [Query](/components/2.0/query/) : the Query component
- [Scheme](/components/2.0/scheme/) : the Scheme component
- [UserInfo](/components/2.0/userinfo/) : the User Information component

List of URI helper class
--------

- [QueryString](/components/2.0/querystring/) : Query string parser and builder
- [UriModifier](/components/2.0/modifiers/) : Partial URI modifier

System Requirements
-------

You need **PHP >= 7.2** but the latest stable version of PHP is recommended

In order to handle IDN host you are required to also install the `intl` extension otherwise an exception will be thrown when attempting to validate such host.

Installation
--------

~~~
$ composer require league/uri-components
~~~

Dependencies
-------

- [League Uri Interfaces](https://github.com/thephpleague/uri-interfaces)
- [PSR-7](http://www.php-fig.org/psr/psr-7/)
