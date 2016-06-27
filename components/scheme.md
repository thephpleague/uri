---
layout: default
title: The Scheme component
---

# The Scheme component

The library provides a `League\Uri\Components\Scheme` class to ease scheme manipulation.

## Instantiation

A new `League\Uri\Components\Scheme` object can be instantiated using its default constructor.

~~~php
<?php

use League\Uri\Components\Scheme;

$scheme = new Scheme('ftp');
echo $scheme; //display 'ftp'

$empty_scheme = new Scheme();
echo $empty_scheme; //display ''
~~~

The scheme component constructor accepts:

- a valid string according to their component validation rules as explain in RFC3986;
- the `null` value;

<p class="message-warning">If the submitted value is not a valid an <code>InvalidArgumentException</code> exception is thrown.</p>

On instantiation the Scheme et normalized using RFC3986 rules (ie: the scheme is lowercased).

### Using a League Uri object

You can acces a `League\Uri\Components\Scheme` object with an already instantiated League Uri object.

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri  = HttpUri::createFromString('http://uri.thephpleague.com:82');
$scheme = $uri->scheme; // $scheme is a League\Uri\Components\Scheme object;
~~~

## Scheme representations

Scheme representations is done using the `UriPart` interface methods:

~~~php
<?php

use League\Uri\Components\Scheme;

$scheme = new Scheme('HtTp');
$scheme->getContent();      //return 'http'
$scheme->__toString();      //return 'http'
$scheme->getUriComponent(); //return 'http:'
~~~

To [compare](/components/overview/#uripartsamevalueas) or [manipulate](/components/overview/##componentmodify) the port object you should refer to the component overview section.