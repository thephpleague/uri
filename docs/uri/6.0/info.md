---
layout: default
title: URI information
---

URI information
=======

The `League\Uri\UriInfo` contains a list of public static methods which returns a list of information regarding the URI object submitted.

## UriInfo::isAbsolute

This public static method tells whether the given URI object represents an absolute URI.

~~~php
<?php

use League\Uri\Http;
use League\Uri\Uri;
use League\Uri\UriInfo;

UriInfo::isAbsolute(Uri::createFromServer($_SERVER)); //returns true
UriInfo::isAbsolute(Http::createFromString("/🍣🍺"));       //returns false
~~~

## UriInfo::isAbsolutePath

This public static method tells whether the given URI object represents an absolute URI path.

~~~php
UriInfo::isAbsolutePath(Uri::createFromServer($_SERVER)); //returns false
UriInfo::isAbsolutePath(Http::createFromString("/🍣🍺"));       //returns true
~~~

## UriInfo::isNetworkPath

This public static method tells whether the given URI object represents an network path URI.

~~~php
UriInfo::isNetworkPath(Http::createFromString("//example.com/toto")); //returns true
UriInfo::isNetworkPath(Uri::createFromString("/🍣🍺")); //returns false
~~~

## UriInfo::isRelativePath

This public static method tells whether the given URI object represents a relative path.

~~~php
UriInfo::isRelativePath(Http::createFromString("🏳️‍🌈")); //returns true
UriInfo::isRelativePath(Http::createFromString("/🍣🍺")); //returns false
~~~

## UriInfo::isSameDocument

This public static method tells whether the given URI object represents the same document.

~~~php
UriInfo::isSameDocument(
    Http::createFromString("example.com?foo=bar#🏳️‍🌈"),
    Http::createFromString("exAMpLE.com?foo=bar#🍣🍺")
); //returns true
~~~