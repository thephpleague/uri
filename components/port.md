---
layout: default
title: The Port component
---

# The Port component

The library provides a `League\Uri\Components\Port` class to ease port manipulation.

## Port creation

A new `League\Uri\Components\Port` object can be instantiated using its default constructor.

~~~php
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
- a int;
- the `null` value;

<p class="message-warning">If the submitted value is not a valid port number an <code>InvalidArgumentException</code> will be thrown.</p>

### Using a League Uri object

Another way to acces a `League\Uri\Components\Port` object is to use an already instantiated League Uri object.

~~~php
use League\Uri\Schemes\Http as HttpUri;

$uri  = HttpUri::createFromString('http://url.thephpleague.com:82');
$port = $uri->port; // $port is a League\Uri\Components\Port object;
~~~

## Port representations

### String representation

Basic port representations is done using the following methods:

~~~php
use League\Uri\Components\Port;

$port = new Port(21);
$port->__toString();      //return '21'
$port->getUriComponent(); //return ':21'
~~~

### Integer representation

A port is a integer between `1` and `65535`. To get the Port as an integer you can use the `Port::toInt` method. This method will return an integer if the port was defined and the `null` value otherwise.

~~~php
use League\Uri\Components\Port;

$port = new Port(81);
$port->toInt(); //return 81;

$empty_port = new Port();
$empty_port->toInt(); //return null
~~~

To [compare](/components/overview/#components-comparison) or [manipulate](/components/overview/#components-modification) the port object you should refer to the component overview section.