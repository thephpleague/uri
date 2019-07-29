---
layout: default
title: The Fragment component
---

# The Fragment component

The library provides a `Fragment` class to ease fragment creation and manipulation.

## Creating a new object

~~~php
<?php
public Fragment::__construct($content = null): void
~~~

<p class="message-notice">submitted string is normalized to be <code>RFC3986</code> compliant.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Exceptions\SyntaxError</code> exception is thrown.</p>

## Properties and methods

This URI component object only exposes the [package common API](/components/2.0/api/).

An additional `decoded` method returns the component value safely decoded.

~~~php
public Fragment::decoded(): ?string
~~~

## Usage

~~~php
<?php

use League\Uri\Components\Fragment;

$fragment = new Fragment('%E2%82%AC');
echo $fragment->getContent(); //display '%E2%82%AC'
echo $fragment->decoded(); //display '€'
echo $fragment;                    //display '%E2%82%AC'
echo $fragment->getUriComponent(); //display '#%E2%82%AC'

$new_fragment = $fragment->getContent(null);
echo $new_fragment->getContent();      //display null
echo $new_fragment;                    //display ''
echo $new_fragment->getUriComponent(); //display ''

$alt_fragment = $fragment->getContent('');
echo $alt_fragment->getContent();      //display ''
echo $alt_fragment;                    //display ''
echo $alt_fragment->getUriComponent(); //display '#'
~~~

<p class="message-notice">The delimiter <code>#</code> is not part of the component value and <strong>must not</strong> be added.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Exceptions\SyntaxError</code> exception is thrown.</p>
