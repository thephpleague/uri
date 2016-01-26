---
layout: default
title: Http URIs
---

# Http, Https URI

## Instantiation

To work with Http URIs you can use the `League\Uri\Schemes\Http` class. This class handles secure and non secure Http URI. In addition to the [defined named constructors](/uri/instantiation/#uri-instantiation), the `Http` class can be instantiated using the server variables.

~~~php
use League\Uri\Schemes\Http as HttpUri;

//don't forget to provide the $_SERVER array
$uri = HttpUri::createFromServer($_SERVER);
~~~

<p class="message-warning">The method only rely on servers safe parameters to determine the current URI. If you are using the library behind a proxy the result may differ from your expectation as no <code>$_SERVER['HTTP_X_*']</code> header is taken into account for security reasons.</p>

## Validation

If a scheme is present and the scheme specific part of a Http URI is not empty the URI can not contain an empty authority. Thus, some Http URI modifications must be applied in a specific order to preserve the URI validation.

~~~php
use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString('http://uri.thephpleague.com/');
echo $uri->withHost('')->withScheme('')->__toString();
// will throw an RuntimeException
// you can not remove the Host if a scheme is present
~~~

Instead you are require to proceed as below

~~~php
use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString('http://uri.thephpleague.com/');
echo $uri->withScheme('')->withHost('')->__toString(); //displays "/"
~~~

<p class="message-notice">When an invalid URI object is created a <code>RuntimeException</code> exception is thrown</p>

## Relation with PSR-7

The `Http` class is compliant with PSR-7 `UriInterface` interface. This means that you can use this class anytime you need a PSR-7 compliant URI object.

## Properties

The Http URI class uses the specialized [HierarchicalPath](/components/hierarchical-path/) class to represents its path. using PHP's magic `__get` method you can access the object path and get more informations about the underlying path.

~~~php
use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString('http://uri.thephpleague.com/uri/schemes/http.md');
echo $uri->path->getBasename();  //display 'http.md'
echo $uri->path->getDirname();   //display '/uri/schemes'
echo $uri->path->getExtension(); //display 'md'
$uri->path->toArray(); //returns an array representation of the path segments
~~~