---
layout: default
title: The Scheme component
---

# The Scheme component

The `Scheme` class eases scheme creation and manipulation. This URI component object only exposes the [package common API](/components/2.0/api/).

## Usage

~~~php
<?php

use League\Uri\Components\Scheme;

$scheme = new Scheme('FtP');
echo $scheme->getContent();      //display 'ftp'
echo $scheme;                    //display 'ftp'
echo $scheme->getUriComponent(); //display 'ftp:'

$new_scheme = $scheme->withContent(null);
echo $new_scheme->getContent();      //display null
echo $new_scheme;                    //display ''
echo $new_scheme->getUriComponent(); //display ''
~~~

<p class="message-notice">The delimiter <code>:</code> is not part of the component value and <strong>must not</strong> be added.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Exceptions\SyntaxError</code> exception is thrown.</p>
