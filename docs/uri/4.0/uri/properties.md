---
layout: default
title: Getting URIs informations
redirect_from:
    - /4.0/uri/properties/
---

# Extracting data from URIs

Even thought the package comes bundle with a serie of URI objects representing usual URI, all theses objects expose the same methods.

## Accessing URI parts and components as strings

You can access the URI string, its individual parts and components using their respective getter methods.

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

Which will lead to the following result for a simple URI:

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString("http://foo:bar@www.example.com:81/how/are/you?foo=baz#title");
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

## Accessing URI parts and components as objects

To access a specific URI part or component as an object you can use PHP's magic method `__get` as follow.

<p class="message-notice">The <code>__get</code> method <strong>is not part</strong> of any interface.</p>

~~~php
<?php

use League\Uri\Schemes\Ws as WsUri;

$uri = WsUri::createFromString("http://foo:bar@www.example.com:81/how/are/you?foo=baz");
$uri->scheme;   //return a League\Uri\Components\Scheme object
$uri->userInfo; //return a League\Uri\Components\UserInfo object
$uri->host;     //return a League\Uri\Components\Host object
$uri->port;     //return a League\Uri\Components\Port object
$uri->path;     //return a League\Uri\Components\HierarchicalPath object
$uri->query;    //return a League\Uri\Components\Query object
$uri->fragment; //return a League\Uri\Components\Fragment object
~~~

Using this technique you can get even more informations regarding your URI.

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString("http://foo:bar@www.example.com:81/how/are/you?foo=baz");
$uri->host->isIp();           //return false the URI uses a registered hostname
$uri->userInfo->getUser();    //return "foo" the user login information
$uri->fragment->__toString(); //return '' because the fragment component is undefined
$uri->path->getBasename();    //return "you"
$uri->query->getValue("foo"); //return "baz"
~~~

<p class="message-notice">The actual methods attach to each component depend on the underlying component object used. For instance a <code>DataUri::path</code> object does not expose the same methods as a <code>Ws::path</code> object would.</p>

To get more informations about component properties refer to the [components documentation](/4.0/components/overview/)

## Getting URI object reference status

<p class="message-notice">New in <code>version 4.2</code></p>

### Description

~~~php
<?php

function League\Uri\uri_reference(mixed $uri [, mixed $base_uri]): array
~~~

This function analyzes the submitted URI object and returns an associative array containing information regarding the URI-reference.

As per [RFC3986](https://tools.ietf.org/html/rfc3986#section-4.1) URI-reference is used to denote the most common usage of a resource identifier. The specification defines 5 possible types of references for any given URI.

- absolute URI
- network path
- absolute path
- relative path
- same document

### Parameters

- `$uri` implements `Psr\Http\Message\UriInterface` or `League\Uri\Interfaces\Uri`
- `$base_uri`optional, implements `Psr\Http\Message\UriInterface` or `League\Uri\Interfaces\Uri`. Required if you want to detect same document reference.

### Returns Values

An associative array is returned. The following keys are always present within the array and their content is always a boolean:

- `absolute_uri`
- `network_path`
- `absolute_path`
- `relative_path`
- `same_document`

### Examples

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;
$uri = HttpUri::createFromString("//스타벅스코리아.com/how/are/you?foo=baz");
$alt_uri = HttpUri::createFromString("//xn--oy2b35ckwhba574atvuzkc.com/how/are/you?foo=baz#bar");

var_dump(League\Uri\uri_reference($uri));
//displays something like
// array(5) {
//   'absolute_uri' => bool(false)
//   'network_path' => bool(true)
//   'absolute_path' => bool(false)
//   'relative_path' => bool(false)
//   'same_document' => bool(false)
// }

var_dump(League\Uri\uri_reference($uri, $alt_uri));
//displays something like
// array(5) {
//   'absolute_uri' => bool(false)
//   'network_path' => bool(true)
//   'absolute_path' => bool(false)
//   'relative_path' => bool(false)
//   'same_document' => bool(true)  //can be true only if a base URI is provided
// }
~~~

## Debugging URI objects

<p class="message-notice">New in <code>version 4.2</code></p>

### __debugInfo

All Uri objects from the package implements PHP5.6+ `__debugInfo` magic method in order to help developpers debug their code. The method is called by `var_dump` and returns an array with an `uri` key whose value is the object string representation.

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString("http://example.com/path?query=value#fragment");

var_dump($uri);
//displays something like
// object(League\Uri\Schemes\Http)#11 (1) {
//     ["uri"]=> string(44) "http://example.com/path?query=value#fragment"
// }
~~~~~~

### __set_state

For the same purpose of debugging and object exportations PHP's magic method `__set_state` is also supported

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString("http://foo:bar@www.example.com:81/how/are/you?foo=baz");
$newUri = eval('return '.var_export($uri, true).';');

$uri->__toString() == $newUri->__toString();
~~~~~~