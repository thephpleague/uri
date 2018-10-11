---
layout: default
title: Manipulating URI
redirect_from:
    - /4.0/uri/manipulations/
---

# Modifying URIs

<p class="message-notice">If the modifications do not alter the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">The method may throw a <code>InvalidArgumentException</code> exception if the resulting URI is not valid for a scheme specific URI.</p>

## Basic modifications

To completely replace one of the URI part you can use the modifying methods exposed by all URI object

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

use League\Uri\Schemes\Ws as WsUri;

$uri = WsUri::createFromString("ws://thephpleague.com/fr/")
    ->withScheme("wss")
    ->withUserInfo("foo", "bar")
    ->withHost("www.example.com")
    ->withPort(81)
    ->withPath("/how/are/you")
    ->withQuery("foo=baz");

echo $uri; //displays wss://foo:bar@www.example.com:81/how/are/you?foo=baz
~~~

## URI modifiers

Often what you really want is to partially update one of the URI component. Using the current public API it is possible but requires several intermediary steps. For instance here's how you would update the query string from a given URI object:

~~~php
<?php

use League\Uri\Components\Query;
use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString("http://www.example.com?foo=toto#~typo");
$uriQuery = new Query($uri->getQuery());
$updateQuery = $uriQuery->merge("foo=bar&taz=");
$newUri = $uri->withQuery($updateQuery->__toString());
echo $newUri; // display http://www.example.com?foo=bar&taz#~typo
~~~

### URI modifiers principles

~~~php
<?php

function(Psr\Http\Message\UriInterface $uri): Psr\Http\Message\UriInterface
//or
function(League\Uri\Interfaces\Uri $uri): League\Uri\Interfaces\Uri
~~~

To ease these operations the package introduces the concept of URI modifiers

A URI modifier:

- must be a callable. If the URI modifier is a class it must implement PHP's `__invoke` method.
- expects its single argument to be an League URI object or a PSR-7 `UriInterface` object and **must return a instance of the submitted object**.
- must be an immutable value object if it is an object.
- are transparent when dealing with error and exceptions. They must not alter of silence them apart from validating their own parameters.

Let's recreate the above example using a URI modifier.

~~~php
<?php

use League\Uri\Components\Query;
use League\Uri\Interfaces\Uri;
use Psr\Http\Message\UriInterface;

$mergeQuery = function ($uri) {
    if (!$uri instanceof Uri && !$uri instanceof UriInterface) {
        throw new InvalidArgumentException(sprintf(
            'Expected data to be a valid URI object; received "%s"',
            (is_object($uri) ? get_class($uri) : gettype($uri))
        ));
    }
    $currentQuery = new Query($uri->getQuery());
    $updatedQuery = $currentQuery->merge('foo=bar&taz')->__toString();

    return $uri->withQuery($updatedQuery);
};
~~~

And now the code becomes:

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString("http://www.example.com?foo=toto#~typo");
$newUri = $mergeQuery($uri);
echo $newUri; // display http://www.example.com?foo=bar&taz#~typo
~~~

The anonymous function `$mergeQuery` is an rough example of a URI modifier. The library `League\Uri\Modifiers\MergeQuery` [provides a better and more suitable implementation](/uri/4.0/uri/manipulation/query/#merging-query-string).

URI Modifiers can be grouped for simplicity in different categories that deals with

- [manipulating the URI](/uri/4.0/uri/manipulation/generic/);
- [manipulating the URI host component](/uri/4.0/uri/manipulation/host/);
- [manipulating the URI path component](/uri/4.0/uri/manipulation/path/);
- [manipulating the URI query component](/uri/4.0/uri/manipulation/query/);
