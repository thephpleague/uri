---
layout: default
title: The Scheme component
redirect_from:
    - /5.0/components/scheme/
---

# The Scheme component

The library provides a `Scheme` class to ease scheme creation and manipulation.

## Creating a new object

~~~php
<?php
public Scheme::__construct(?string $content = null): void
~~~

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Components\Exception</code> exception is thrown.</p>

The `League\Uri\Components\Exception` extends PHP's SPL `InvalidArgumentException`.

## Properties and methods

This URI component object only exposes the [package common API](/components/1.0/api/).

## Usage

~~~php
<?php

use League\Uri\Components\Scheme;

$scheme = new Scheme('FtP');
$scheme->isNull();  //return false
$scheme->isEmpty(); //return false
echo $scheme->getContent();                         //display 'ftp'
echo $scheme->getContent(Scheme::RFC3986_ENCODING); //display 'ftp'
echo $scheme->getContent(Scheme::RFC3987_ENCODING); //display 'ftp'
echo $scheme->getContent(Scheme::NO_ENCODING);      //display 'ftp'
echo $scheme;                    //display 'ftp'
echo $scheme->getUriComponent(); //display 'ftp:'

$new_scheme = $scheme->withContent(null);
$new_scheme->isNull();  //return true
$new_scheme->isEmpty(); //return true
echo $new_scheme->getContent();      //display null
echo $new_scheme;                    //display ''
echo $new_scheme->getUriComponent(); //display ''
~~~

<p class="message-notice">The delimiter <code>:</code> is not part of the component value and <strong>must not</strong> be added.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Components\Exception</code> exception is thrown.</p>
