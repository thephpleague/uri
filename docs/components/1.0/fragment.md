---
layout: default
title: The Fragment component
redirect_from:
    - /5.0/components/fragment/
---

# The Fragment component

The library provides a `Fragment` class to ease fragment creation and manipulation.

## Creating a new object

~~~php
<?php
public Fragment::__construct(?string $content = null): void
~~~

<p class="message-notice">submitted string is normalized to be <code>RFC3986</code> compliant.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Components\Exception</code> exception is thrown.</p>

The `League\Uri\Components\Exception` extends PHP's SPL `InvalidArgumentException`.

## Properties and methods

This URI component object only exposes the [package common API](/components/1.0/api/).

## Usage

~~~php
<?php

use League\Uri\Components\Fragment;

$fragment = new Fragment('%E2%82%AC');
$fragment->isNull(); //returns false
$fragment->isEmpty(); //return false
echo $fragment->getContent(Fragment::RFC3986_ENCODING); //display '%E2%82%AC'
echo $fragment->getContent(Fragment::RFC3987_ENCODING); //display '€'
echo $fragment->getContent(Fragment::NO_ENCODING);      //display '€'
echo $fragment;                    //display '%E2%82%AC'
echo $fragment->getUriComponent(); //display '#%E2%82%AC'

$new_fragment = $fragment->getContent(null);
$new_fragment->isNull(); //returns true
$new_fragment->isEmpty(); //return true
echo $new_fragment->getContent();      //display null
echo $new_fragment;                    //display ''
echo $new_fragment->getUriComponent(); //display ''

$alt_fragment = $fragment->getContent('');
$alt_fragment->isNull(); //returns false
$alt_fragment->isEmpty(); //return true
echo $alt_fragment->getContent();      //display ''
echo $alt_fragment;                    //display ''
echo $alt_fragment->getUriComponent(); //display '#'
~~~

<p class="message-notice">The delimiter <code>#</code> is not part of the component value and <strong>must not</strong> be added.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Components\Exception</code> exception is thrown.</p>