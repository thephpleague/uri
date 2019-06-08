---
layout: default
title: Http URIs
---

# PSR-7 URI

The `League\Uri\Http` class implements:

- PSR-7 [UriInterface](https://www.php-fig.org/psr/psr-7/#35-psrhttpmessageuriinterface)
- PHP's [JsonSerializable](https://php.net/jsonserializable) interfaces.

Following the PSR-7 interfaces the class handles all URI schemes but default to processing them as HTTP(s) URI if the scheme is not defined.

## Instantiation

<p class="message-warning">The default constructor is private and can not be accessed to instantiate a new object.</p>

The `League\Uri\Http` class comes with the following named constructor to ease instantiation.

### Using a string

~~~php
<?php

use League\Uri\Http;

$uri = Http::createFromString('http://example.com/path/to?q=foo%20bar#section-42');
~~~

<p class="message-info">The method supports parameter widening, scalar values and objects implementing the <code>__toString</code> are accepted.</p>

### Using Uri components

~~~php
$uri = Http::createFromComponents(parse_url("http://uri.thephpleague/5.0/uri/api"));
~~~

<p class="message-warning">If you supply your own hash to <code>createFromComponents</code>, you are responsible for providing well parsed components without their URI delimiters.</p>

### Using the environment variables

~~~php
//don't forget to provide the $_SERVER array
$uri = Http::createFromServer($_SERVER);
~~~

<p class="message-warning">The method only relies on the server's safe parameters to determine the current URI. If you are using the library behind a proxy the result may differ from your expectation as no <code>$_SERVER['HTTP_X_*']</code> header is taken into account for security reasons.</p>

### Using a base URI

~~~php
$uri = Http::createFromBaseUri('./p#~toto', 'http://thephpleague.com/uri/5.0/uri/');
echo $uri; //returns 'http://thephpleague.com/uri/5.0/uri/p#~toto'
~~~

The `createFromBaseUri` named constructor instantiates an absolute URI or resolves a relative URI against another absolute URI. If present the absolute URI can be:

- a League `UriInterface` object
- a `PSR-7` `UriInterface` object
- an object implementing the `__toString` method
- a scalar

Exceptions are thrown if:

- the provided base URI is not absolute;
- the provided URI is not absolute in absence of a base URI;

When a base URI is given the URI is resolved against that base URI just like a browser would for a relative URI.

<p class="message-info">The method supports parameter widening, scalar values and objects implementing the <code>__toString</code> or other URI objects are accepted.</p>

### Using a URI object

The `createFromUri` named constructor instantiates an URI from a `League\Uri\Contracts\UriInterface` implementing object or another PSR-7 `UriInterface` implementing object

~~~php
<?php

use League\Uri\Http;
use League\Uri\Uri;

$rfc3986Uri = Uri::createFromDataPath(__DIR__.'/data/logo.png');
$uri = Http::createFromUri($rfc3986Uri);
~~~

## Validation

If no scheme is present, the URI is treated as a HTTP(s) URI and must follow the scheme rules as explained in RFC3986 and PSR-7 documentation.

### Authority presence

If a scheme is present and the scheme specific part of a Http URI is not empty the URI can not contain an empty authority. Thus, some Http URI modifications must be applied in a specific order to preserve the URI validation.

~~~php
$uri = Http::createFromString('http://uri.thephpleague.com/');
echo $uri->withHost('')->withScheme('');
// will throw an League\Uri\UriException
// you can not remove the Host if a scheme is present
~~~

Instead you are required to proceed as below

~~~php
$uri = Http::createFromString('http://uri.thephpleague.com/');
echo $uri->withScheme('')->withHost(''); //displays "/"
~~~

<p class="message-notice">When an invalid URI object is created an <code>UriException</code> exception is thrown</p>


### Path validity

According to RFC3986, if an HTTP URI contains a non empty authority part, the URI path must be the empty string or absolute. Thus, some modification may trigger an <code>UriException</code>.

~~~php
$uri = Http::createFromString('http://uri.thephpleague.com/');
echo $uri->withPath('uri/schemes/http');
// will throw an League\Uri\UriException
~~~

Instead you are required to submit a absolute path

~~~php
$uri = Http::createFromString('http://uri.thephpleague.com/');
echo $uri->withPath('/uri/schemes/http'); // displays 'http://uri.thephpleague.com/uri/schemes/http'
~~~

Of note this does not mean that rootless path are forbidden, the following code is fine.

~~~php
$uri = Http::createFromString('?foo=bar');
echo $uri->withPath('uri/schemes/http'); // displays 'uri/schemes/http?foo=bar'
~~~

## Json representation

The json representation of the class is the same as its string representation per PSR-7 rules.

~~~php
$uri = Http::createFromString('http://uri.thephpleague.com/');
echo json_encode($uri);
// will display "http://uri.thephpleague.com/" the double quote are added by the json function
~~~

## Relation with PSR-7

The `Http` class implements the PSR-7 `UriInterface` interface. This means that you can use this class anytime you need a PSR-7 compliant URI object.
