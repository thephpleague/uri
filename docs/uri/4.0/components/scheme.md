---
layout: default
title: The Scheme component
    - /4.0/components/scheme/
---

# The Scheme component

The library provides a `Scheme` class to ease scheme creation and manipulation.

## Instantiation

### Using the default constructor.

~~~php
<?php

public function __contruct($scheme = null)
~~~

The constructor accepts:

- a valid string according to RFC3986 rules;
- the `null` value;

#### Example

~~~php
<?php

use League\Uri\Components\Scheme;

$scheme = new Scheme('ftp');
echo $scheme->getContent();      //display 'ftp'
echo $scheme;                    //display 'ftp'
echo $scheme->getUriComponent(); //display 'ftp:'

$scheme = new Scheme();
echo $scheme->getContent();      //display null
echo $scheme;                    //display ''
echo $scheme->getUriComponent(); //display ''
~~~

<p class="message-warning">If the submitted value is not a valid an <code>InvalidArgumentException</code> exception is thrown.</p>

<p class="message-info">On instantiation the submitted string is normalized using RFC3986 rules.</p>

### Using a League Uri object

You can access a `Scheme` object with an already instantiated Uri object.

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString('http://uri.thephpleague.com:82');
$scheme = $uri->scheme; // $scheme is a League\Uri\Components\Scheme object;
~~~

## Properties and Methods

The component representation, comparison and manipulation is done using the package [UriPart](/uri/4.0/components/overview/#uri-part-interface) and the [Component](/uri/4.0/components/overview/#uri-component-interface) interfaces.