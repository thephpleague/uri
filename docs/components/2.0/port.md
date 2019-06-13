---
layout: default
title: The Port component
---

# The Port component

The library provides a `Port` class to ease port manipulation.

## Creating a new object

~~~php
public Port::__construct(?int $content = null): void
~~~

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Exceptions\SyntaxError</code> exception is thrown.</p>

## Properties and methods

This URI component object exposes the [package common API](/components/2.0/api/).

An additional `toInt` method returns the component value as an integer or `null` if the component is not defined.

~~~php
public Port::toInt(): ?int
~~~

## Usage

~~~php
<?php

use League\Uri\Components\Port;

$port = new Port(443);
$port->getContent();           //returns (int) 443

echo $port;                    //displays '443'
echo $port->getUriComponent(); //displays ':443'
$port->toInt();                // returns 443

$new_port = $port->withContent(null);
$new_port->getContent();           //returns null
$new_port->toInt();                //returns null
echo $new_port;                    //displays ''
echo $new_port->getUriComponent(); //displays ''
~~~

<p class="message-notice">The delimiter <code>:</code> is not part of the component value and <strong>must not</strong> be added.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Exceptions\SyntaxError</code> exception is thrown.</p>
