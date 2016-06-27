---
layout: default
title: The Pass component
---

# The Pass component

The library provides a `League\Uri\Components\Pass` class to ease user manipulation.

## Instantiation

### Default Constructor

A new `League\Uri\Components\Pass` object can be instantiated using its default constructor.

~~~php
<?php

use League\Uri\Components\Pass;

$component = new Pass('ftp');
echo $component; //display 'ftp'

$empty_component = new Pass();
echo $empty_component; //display ''
~~~

The scheme component constructor accepts:

- a valid string according to their component validation rules as explain in RFC3986;
- the `null` value;

<p class="message-warning">If the submitted value is not a valid an <code>InvalidArgumentException</code> exception is thrown.</p>

On instantiation the Pass is normalized using RFC3986 rules.

### Using a League Uri object

You can acces a `League\Uri\Components\Pass` object with an already instantiated League Uri object.

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString('http://uri.thephpleague.com:82');
$component = $uri->userInfo->pass; // $pass is a League\Uri\Components\Pass object;
~~~

## Pass representations

### UriPart representations

Pass representations is done using the `UriPart` interface methods:

~~~php
<?php

use League\Uri\Components\Pass;

$component = new Pass('HtTp');
$component->getContent();      //return 'http'
$component->__toString();      //return 'http'
$component->getUriComponent(); //return 'http'
~~~

<p class="message-notice"><code>getContent</code> added in <code>version 4.2</code></p>

### Pass::getValue

<p class="message-notice">New since <code>version 4.2</code></p>

Returns the decoded value of a Pass component

~~~php
<?php

public Pass::getValue(void): string
~~~

#### Example

~~~php
<?php

use League\Uri\Components\Pass;

$component = new Pass('%E2%82%AC');
echo $component->getUriComponent(); //displays '%E2%82%AC'
echo $component->getValue(); //displays '€'
~~~

To [compare](/components/overview/#uripartsamevalueas) or [manipulate](/components/overview/##componentmodify) the port object you should refer to the component overview section.