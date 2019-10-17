---
layout: default
title: Upgrading from 1.x to 2.x
---

# Upgrading from 1.x to 2.x

`uri-components:2.0` is a new major version that comes with backward compatibility breaks.

This guide will help you migrate from a 1.x version to 2.0. It will only explain backward incompatibility breaks, it will not present the new features ([read the documentation for that](/components/2.0/)).

## Installation

If you are using composer then you should update the require section of your `composer.json` file.

~~~
composer require league/uri-components:^2.0
~~~

This will edit (or create) your `composer.json` file.

## PHP version requirement

`uri-components:2.0` requires a PHP version greater or equal than 7.2.0 (was previously 7.0.0).

## Package replacements and conflicts

This package:

- replaces and deprecates without conflicting the `uri-query-parser` package.
- partially replaces and deprecates without conflicting the `uri-manipulation` package.

## Removed features

### Host Public Suffix Resolution

This package no longer expose API to resolve Public Suffix List in Host. We recommend using a dedicated package for that like [PHP Domain Parser](https://github.com/jeremykendall/php-domain-parser).

### Everything is final

Components objects are now all marked as final and implements at least one interface.

To use them you can either typehint against their interfaces or against a specific implementation but you can no longer extend them.

### UriComponentInterface

Because this new package targets PHP7.2+ the:
 
- `UriComponentInterface::isEmpty`; 
- `UriComponentInterface::isNull`;

methods are removed in favor of `UriComponentInterface::getContent()` method.

Also, the `UriComponentInterface::getContent()` method no longer takes any parameter and always returns the RFC3986 version of the URI component.

### Host

The `Host` object no longer is iterable or exposes label related methods. To be able to access the host lables you need to instantiate a `Domain` object.
All information regarding Public Suffix information has been removed from the package.

### Query

The `Query` object public API has been updated to better match the [WHATWG URL living standard URLSearchParams class](https://url.spec.whatwg.org/#interface-urlsearchparams).
You will need to upgrade your code to match this specification expected values.
