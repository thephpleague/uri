---
layout: default
title: Getting URIs informations
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

To get more informations about component properties refer to the [components documentation](/components/overview/)

## Debugging URI objects

<p class="message-notice">New in <code>version 4.2</code></p>

### __debugInfo

All Uri objects from the package implements PHP5.6+ `__debugInfo` magic method in order to help developpers debug their code. The method is called by `var_dump` and displays the Uri components and the Uri string representations.

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString("http://example.com/path?query=value#fragment");

var_dump($uri);
//displays something like
// object(League\Uri\Schemes\Http)#11 (8) {
//     ["uri"]=> string(44) "http://example.com/path?query=value#fragment"
//     ["scheme"]=> string(4) "http"
//     ["userInfo"]=> string(0) ""
//     ["host"]=> string(11) "example.com"
//     ["port"]=> NULL
//     ["path"]=> string(5) "/path"
//     ["query"]=> string(11) "query=value"
//     ["fragment"]=> string(8) "fragment"
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