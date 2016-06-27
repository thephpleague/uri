---
layout: default
title: The Fragment component
---

# The Fragment component

The library provides a `League\Uri\Components\Fragment` class to ease fragment manipulation.

## Instantiation

A new `League\Uri\Components\Fragment` object can be instantiated using its default constructor.

~~~php
<?php

use League\Uri\Components\Fragment;

$scheme = new Fragment('ftp');
echo $scheme; //display 'ftp'

$empty_scheme = new Fragment();
echo $empty_scheme; //display ''
~~~

The scheme component constructor accepts:

- a valid string according to their component validation rules as explain in RFC3986;
- the `null` value;

<p class="message-warning">If the submitted value is not a valid an <code>InvalidArgumentException</code> exception is thrown.</p>

### Using a League Uri object

You can acces a `League\Uri\Components\Fragment` object with an already instantiated League Uri object.

~~~php
<?php

use League\Uri\Fragments\Http as HttpUri;

$uri  = HttpUri::createFromString('http://uri.thephpleague.com:82');
$scheme = $uri->scheme; // $scheme is a League\Uri\Components\Fragment object;
~~~

## Fragment representations

### UriPart representation

Fragment representations is done using the `UriPart` interface methods:

~~~php
<?php

use League\Uri\Components\Fragment;

$component = new Fragment('HtTp');
$component->getContent();      //return 'HtTp'
$component->__toString();      //return 'HtTp'
$component->getUriComponent(); //return '#HtTp'
~~~

### Fragment::getValue

<p class="message-notice">New since <code>version 4.2</code></p>

Returns the decoded value of a fragment component

~~~php
<?php

public Fragment::getValue(void): string
~~~

#### Example

~~~php
<?php

use League\Uri\Components\Fragment;

$component = new Fragment('%E2%82%AC');
echo $component->getUriComponent(); //displays '#%E2%82%AC'
echo $component->getValue(); //displays '€'
~~~

To [compare](/components/overview/#uripartsamevalueas) or [manipulate](/components/overview/##componentmodify) the port object you should refer to the component overview section.