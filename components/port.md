---
layout: default
title: The Port component
---

# The Port component

The library provides a `League\Uri\Components\Port` class to ease port manipulation.

## Instantiation

A new `League\Uri\Components\Port` object can be instantiated using its default constructor.

~~~php
<?php

use League\Uri\Components\Port;

$port = new Port(443);
echo $port; //display '443'

$string_port = new Port('443');
echo $string_port; //display '443'

$empty_port = new Port();
echo $empty_port; //display ''
~~~

The port component constructor accepts:

- a valid string according to their component validation rules as explain in RFC3986;
- a integer between `1` and `65535`;
- the `null` value;

<p class="message-warning">If the submitted value is not a valid port number an <code>InvalidArgumentException</code> exception is thrown.</p>

### Using a League Uri object

You can acces a `League\Uri\Components\Port` object with an already instantiated League Uri object.

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri  = HttpUri::createFromString('http://uri.thephpleague.com:82');
$port = $uri->port; // $port is a League\Uri\Components\Port object;
~~~

## Port representations

Basic port representations is done using the `UriPart` interface methods:

~~~php
<?php

use League\Uri\Components\Port;

$port = new Port(21);
$port->getContent();      //return '21'
$port->__toString();      //return '21'
$port->getUriComponent(); //return ':21'
~~~

### Port::toInt

<p class="message-warning">Since <code>version 4.2</code> this method is deprecated please use <code>Port::getContent</code> instead.</p>

Return an integer if the port was defined or `null` otherwise.

~~~php
<?php

use League\Uri\Components\Port;

$port = new Port(81);
$port->toInt(); //return 81;

$empty_port = new Port();
$empty_port->toInt(); //return null
~~~

To [compare](/components/overview/#components-comparison) or [manipulate](/components/overview/#components-modification) the port object you should refer to the component overview section.