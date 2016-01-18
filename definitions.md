---
layout: default
title: URIs as Value Objects
---

# Terminology

The library models <abbr title="Uniform Resource Identifier">URIs</abbr> and URIs components as [immutable](http://en.wikipedia.org/wiki/Immutable_object) [value objects](http://en.wikipedia.org/wiki/Value_object).

## URI, URL and URN

Often you will encounter in the public spaces the following terms:

- URI which stands for *Uniform Resource Identifier*;
- URL which stands for *Uniform Resource Locator*;
- URN which stands for *Uniform Resource Name*;  

But according to [RFC3986](http://tools.ietf.org/html/rfc3986#section-1.1.3)

> Future specifications and related documentation should use the general term "URI" rather than the more restrictive terms "URL" and "URN".

This is the reason why you will mostly encounter the term URI throughout the documentation to highlight the fact that the package is not limited to HTTP(S) URI.

## Value Objects

> The term "Uniform Resource Locator" (URL) refers to the subset of URIs that, in addition to identifying a resource, provide a means of locating the resource by describing its primary access mechanism. [RFC3986](http://tools.ietf.org/html/rfc3986#section-1.1.3)

This means that a URI is like a street address, if you omit or change even a single character in it, you won't be able to clearly identify what your are looking for. This is exactly the definition of a value object.

~~~php
use League\Uri\Schemes\Http as HttpUri;

$uri1 = HttpUri::createFromString("http://example.com:81/toto");
$uri2 = HttpUri::createFromString("http://example.com:82/toto");
//represent 2 different URIs with different port component.
$uri1->__toString() == $uri2->__toString(); //return false;
~~~

## Immutability

To ease and ensure the integrity of the value, when a component is altered instead of modifying its current value, we return a new component with the changed value. This practice is called immutability.

~~~php
use League\Uri\Schemes\Http as HttpUri;

$uri1 = HttpUri::createFromString("http://example.com:81/toto");
$uri2 = $uri1->withPort(82);
echo $uri1; //still displays "http://example.com:81/toto"
echo $uri2; //displays "http://example.com:82/toto"
~~~

With both of these concepts, the package enforces stronger and efficient manipulation of URIs and its different components.