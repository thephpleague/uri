---
layout: default
title: Uri Object API
---

URI Objects API
=======

Creating new URI objects
-------

### URI instantiation

To instantiate a new URI object you can use two named constructors:

~~~php
<?php
public static Uri::createFromString(string $uri = ''): Uri
public static Uri::createFromComponents(array $components): Uri
~~~

- The `Uri::createFromString` named constructor returns an new URI object from a string.
- The `Uri::createFromComponents` named constructor returns an new URI object from the return value of PHP’s function `parse_url`.

<p class="message-warning">If you supply your own hash to <code>createFromComponents</code>, you are responsible for providing well parsed components without their URI delimiters.</p>

### URI validation

A `League\Uri\Schemes\UriException` exception is triggered if an invalid URI is given.

~~~php
<?php

use League\Uri\Schemes\Data;

$uri = Data::createFromComponents(
    parse_url("http://uri.thephpleague/5.0/uri/api")
);
// throws a League\Uri\Schemes\UriException
// because the http scheme is not supported
~~~

Because `createFromString` internally use `League\Uri\Parser` if the supplied URI string is invalid a `League\Uri\Exception` can be thrown on instantiation.

~~~php
<?php

use League\Uri\Schemes\Http;

$uri = Http::createFromString(':');
// throws a League\Uri\Exception
// because the URI string is invalid
~~~

<p class="message-info">Because the <code>League\Uri\Schemes\UriException</code> exception extends <code>League\Uri\Exception</code> you can catch any exception triggered by the package using the following code.</p>

<p class="message-info"><code>League\Uri\Exception</code> extends PHP's SPL <code>InvalidArgumentException</code>.</p>


~~~php
<?php

use League\Uri\Exception;
use League\Uri\Schemes\Http;

try {
	$uri = Http::createFromString(':');
} catch (Exception $e) {
	//$e is either League\Uri\Exception
	//or League\Uri\Schemes\UriException
}
~~~


Accessing URI properties
-------

All URI objects expose the same methods. You can access the URI string, its individual parts and components using their respective getter methods.

~~~php
<?php

public Uri::__toString(): string
public Uri::getScheme(void): string
public Uri::getUserInfo(void): string
public Uri::getHost(void): string
public Uri::getPort(void): int|null
public Uri::getAuthority(void): string
public Uri::getPath(void): string
public Uri::getQuery(void): string
public Uri::getFragment(void): string
~~~

Which will lead to the following result for a simple HTTP URI:

~~~php
<?php

use League\Uri\Schemes\Http;

$uri = Http::createFromString("http://foo:bar@www.example.com:81/how/are/you?foo=baz#title");
echo $uri;                 //displays "http://foo:bar@www.example.com:81/how/are/you?foo=baz#title"
echo $uri->getScheme();    //displays "http"
echo $uri->getUserInfo();  //displays "foo:bar"
echo $uri->getHost();      //displays "www.example.com"
echo $uri->getPort();      //displays 81 as an integer
echo $uri->getAuthority(); //displays "foo:bar@www.example.com:81"
echo $uri->getPath();      //displays "/how/are/you"
echo $uri->getQuery();     //displays "foo=baz"
echo $uri->getFragment();  //displays "title"
~~~

Modifying URI properties
-------

To replace one of the URI part you can use the modifying methods exposed by all URI object. If the modifications do not alter the current object, it is returned as is, otherwise, a new modified object is returned.

<p class="message-notice">Any modification method can trigger a <code>League\Uri\Schemes\UriException</code> exception if the resulting URI is not valid. Just like with the instantiation methods, validition is scheme dependant.</p>

~~~php
<?php

public Uri::withScheme(string $scheme): self
public Uri::withUserInfo(string $user [, string $password = null]): self
public Uri::withHost(string $host): self
public Uri::withPort(int|null $port): self
public Uri::withPath(string $path): self
public Uri::withQuery(string $query): self
public Uri::withFragment(string $fragment): self
~~~

Since All URI object are immutable you can chain each modifying methods to simplify URI creation and/or modification.

~~~php
<?php

use League\Uri\Schemes\Ws;

$uri = Ws::createFromString("ws://thephpleague.com/fr/")
    ->withScheme("wss")
    ->withUserInfo("foo", "bar")
    ->withHost("www.example.com")
    ->withPort(81)
    ->withPath("/how/are/you")
    ->withQuery("foo=baz");

echo $uri; //displays wss://foo:bar@www.example.com:81/how/are/you?foo=baz
~~~

URI normalization
-------

Out of the box the package normalizes any given URI according to the non destructive rules of RFC3986.

These non destructives rules are:

- scheme and host components are lowercased;
- the host is converted to its ascii representation using punycode if needed
- query, path, fragment components are URI encoded if needed;
- the port number is removed from the URI string representation if the standard port is used;

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString("hTTp://www.ExAmPLE.com:80/hello/./wor ld?who=f 3#title");
echo $uri; //displays http://www.example.com/hello/./wor%20ld?who=f%203#title

$uri = HttpUri::createFromComponent(parse_url("hTTp://www.bébé.be?#"));
echo $uri; //displays http://xn--bb-bjab.be?#
~~~
