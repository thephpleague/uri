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

## UriInfo::getOrigin

This public static method returns the URI origin as defined by the [WHATWG URL Living standard](https://url.spec.whatwg.org/#origin)

~~~php
<?php

use League\Uri\Http;
use League\Uri\Uri;
use League\Uri\UriInfo;

UriInfo::getOrigin(Http::createFromString('https://uri.thephpleague.com/uri/6.0/info/')); //returns 'https://uri.thephpleague.com'
UriInfo::getOrigin(Uri::createFromString('blob:https://mozilla.org:443')); //returns 'https://mozilla.org'
UriInfo::getOrigin(Http::createFromString('file:///usr/bin/php')); //returns null
UriInfo::getOrigin(Uri::createFromString('data:text/plain,Bonjour%20le%20monde%21')); //returns null
~~~

<p class="message-info">For absolute URI with the <code>file</code> scheme the method will return <code>null</code> (as this is left to the implementation decision)</p>

Because the origin property does not exists in the RFC3986 specification this additional steps is implemented:

- For non absolute URI the method will return `null`

~~~php
<?php

use League\Uri\Http;
use League\Uri\UriInfo;

UriInfo::getOrigin(Http::createFromString('/path/to/endpoint')); //returns null
~~~

## UriInfo::isCrossOrigin

This public static method tells whether the given URI object represents different origins. 
According to [RFC9110](https://www.rfc-editor.org/rfc/rfc9110#section-4.3.1) The "origin" for a given URI is the triple of scheme, host, and port 
after normalizing the scheme and host to lowercase and normalizing the port to remove any leading 
zeros.

~~~php
<?php

use GuzzleHttp\Psr7\Utils;
use League\Uri\Http;
use League\Uri\Uri;
use League\Uri\UriInfo;
use Nyholm\Psr7\Uri;

UriInfo::isCrossOrigin(
    Utils::uriFor('blob:http://xn--bb-bjab.be./path'),
    new Uri('http://Bébé.BE./path'),
); // returns false

UriInfo::isCrossOrigin(
    Http::createFromString('https://example.com/123'), 
    Uri::createFromString('https://www.example.com/')
); // returns true
~~~

The method expects URI objects (implementing the League or PSR-7  `UriInterface`). 
The method takes into account i18n while comparing both URI id the `intl-extension` is installed.
