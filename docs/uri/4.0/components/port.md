---
layout: default
title: The Port component
    - /4.0/components/port/
---

# The Port component

The library provides a `Port` class to ease port manipulation.

## Instantiation

### Using the default constructor.

~~~php
<?php

public function __contruct(int $port = null)
~~~

The constructor accepts:

- a valid string according to their component validation rules as explain in RFC3986;
- a integer between `1` and `65535`;
- the `null` value;

#### Examples

~~~php
<?php

use League\Uri\Components\Port;

$port = new Port(443);
$port->getContent();           //return (int) 443
echo $port;                    //display '443'
echo $port->getUriComponent(); //display ':443'

$string_port = new Port('443');
$string_port->getContent();           //return (int) 443
echo $string_port;                    //display '443'
echo $string_port->getUriComponent(); //display ':443'

$empty_port = new Port();
$empty_port->getContent();           //return null
echo $empty_port;                    //display ''
echo $empty_port->getUriComponent(); //display ''
~~~

<p class="message-warning">If the submitted value is not a valid port number an <code>InvalidArgumentException</code> exception is thrown.</p>

<p class="message-info">On instantiation the submitted string is normalized using RFC3986 rules.</p>

### Using a League Uri object

You can acces a `Port` object with an already instantiated League `Uri` object.

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri  = HttpUri::createFromString('http://uri.thephpleague.com:82');
$port = $uri->port; // $port is a League\Uri\Components\Port object;
~~~

## Properties and Methods

The component representation, comparison and manipulation is done using the package [UriPart](/uri/4.0/components/overview/#uri-part-interface) and the [Component](/uri/4.0/components/overview/#uri-component-interface) interfaces methods.

### Port::toInt

<p class="message-warning">Since <code>version 4.2</code> this method is deprecated please use <code>Port::getContent</code> instead.</p>

Return an integer if the port was defined or `null` otherwise.

~~~php
<?php

use League\Uri\Components\Port;

$port = new Port(81);
$port->toInt(); //return (int) 81

$empty_port = new Port();
$empty_port->toInt(); //return null
~~~