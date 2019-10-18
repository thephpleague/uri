---
layout: default
title: Upgrading from 5.x to 6.x
---

# Upgrading from 5.x to 6.x

`League\Uri 6.0` is a new major version that comes with backward compatibility breaks.

This guide will help you migrate from a 4.x version to 5.0. It will only explain backward compatibility breaks, it will not present the new features ([read the documentation for that](/6.0/)).

## Installation

If you are using composer then you should update the require section of your `composer.json` file.

~~~
composer require league/uri:^6.0
~~~

This will edit (or create) your `composer.json` file.

## PHP version requirement

`League\Uri 6.0` requires a PHP version greater or equal than 7.2.0 (was previously 7.0.0).

## Package replacements and conflicts

This package is no longer a meta-package as such:

- it replaces, deprecates and conflicts with the `uri-schemes` package.
- it replaces and deprecates without conflicting the `uri-parser` package.
- it partially replaces and deprecates without conflicting the `uri-manipulation` package.

You are required to be explicit about your dependencies and 
require the `uri-components` v2.0 package separately to get access to 
URI components objects and how to manipulate them with URI objects.

## Interfaces

The `League\Uri\Http` class no longer implements the `League\Uri` specific interface. It only implements the PSR-7 `UriInterface` and PHP's `JsonSerializable` interfaces.

## All implementations are final

Prior to this version, you could extends the URI objects or use an `AbstractUri` object to create your own URI object. In this new version all classes are finals and you should use the decorator pattern or implement your own objects if you want to support other specific schemes.

## URI Schemes support

The `League\Uri\Http` is no longer restricted to http(s) schemes it can be used for any schemes but differ to http(s) scheme validation if the URI has no scheme or if this scheme is special.

Before:

~~~php
<?php

use League\Uri\Http;

$uri = Http::createFromString('ftp://uri.thephpleague.com/upgrading/');
//triggers an exception
~~~

After:

~~~php
<?php

use League\Uri\Http;

$uri = Http::createFromString('ftp://uri.thephpleague.com/upgrading/');
echo $uri; //displays 'ftp://uri.thephpleague.com/upgrading/'
~~~

All URI objects classes have been removed from the package except for the `League\Uri\Uri` and `League\Uri\Http` classes.

Before:

~~~php
<?php

use League\Uri\Ftp;

$uri = Ftp::createFromString('ftp://uri.thephpleague.com/upgrading/');
~~~

After:

~~~php
<?php

use League\Uri\Uri;
use League\Uri\Http;

$uri = Uri::createFromString('ftp://uri.thephpleague.com/upgrading/');
//or
$uri = Http::createFromString('ftp://uri.thephpleague.com/upgrading/');
~~~

The choice of class usage will depends on your business rules.

## Parameter acceptance

Prior to this release to undefined a component content you needed to set it with the empty string `''` starting with this version when using the `League\Uri\Uri` object, undefined component are defined with the `null` value except for the path component which can never be `null`.

Before:

~~~php
<?php

use League\Uri\Uri;

$uri = Uri::createFromString('ftp://uri.thephpleague.com/upgrading/');
$newUri = $uri->withScheme('');
echo $newUri; //displays '//uri.thephpleague.com/upgrading/'
~~~

After:

~~~php
<?php

use League\Uri\Uri;

$uri = Uri::createFromString('ftp://uri.thephpleague.com/upgrading/');
$newUri = $uri->withScheme(null);
echo $newUri; //displays '//uri.thephpleague.com/upgrading/'
~~~

<p class="message-warning"><b>WARNING</b> for the scheme component this change will trigger an exception.</p>

~~~php
<?php

use League\Uri\Uri;

$uri = Uri::createFromString('ftp://uri.thephpleague.com/upgrading/');
$uri->withScheme(''); // will trigger an League\Uri\Exceptions\SyntaxError exception
~~~
