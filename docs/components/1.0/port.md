---
layout: default
title: The Port component
redirect_from:
    - /5.0/components/port/
---

# The Port component

The library provides a `Port` class to ease port manipulation.

## Creating a new object

~~~php
<?php
public Port::__construct(?int $content = null): void
~~~

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Components\Exception</code> exception is thrown.</p>

The `League\Uri\Components\Exception` extends PHP's SPL `InvalidArgumentException`.

## Properties and methods

This URI component object only exposes the [package common API](/components/1.0/api/).

## Usage

~~~php
<?php

use League\Uri\Components\Port;

$port = new Port(443);
$port->isNull();  //return false
$port->isEmpty(); //return false
$port->getContent();                       //return (int) 443
$port->getContent(Port::RFC3986_ENCODING); //return (int) 443
$port->getContent(Port::RFC3987_ENCODING); //return (int) 443
$port->getContent(Port::NO_ENCODING);      //return (int) 443
echo $port;                    //display '443'
echo $port->getUriComponent(); //display ':443'

$new_port = $port->withContent(null);
$new_port->isNull();  //return true
$new_port->isEmpty(); //return true
$new_port->getContent();           //return null
echo $new_port;                    //display ''
echo $new_port->getUriComponent(); //display ''
~~~

<p class="message-notice">The delimiter <code>:</code> is not part of the component value and <strong>must not</strong> be added.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Components\Exception</code> exception is thrown.</p>
