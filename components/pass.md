---
layout: default
title: The Pass component
---

# The Pass component

The library provides a `Pass` class to ease user creation and manipulation.

## Instantiation

### Using the default constructor.

~~~php
<?php

public function __contruct($pass = null)
~~~

The constructor accepts:

- a valid string according to RFC3986 rules;
- the `null` value;

<p class="message-warning">If the submitted value is not a valid an <code>InvalidArgumentException</code> exception is thrown.</p>

#### Example

~~~php
<?php

use League\Uri\Components\Pass;

$user = new Pass('john');
echo $user->getContent();      //display 'john'
echo $user;                    //display 'john'
echo $user->getUriComponent(); //display 'john'

$user = new Pass();
echo $user->getContent();      //display null
echo $user;                    //display ''
echo $user->getUriComponent(); //display ''
~~~

<p class="message-info">On instantiation the user is encoded using RFC3986 rules.</p>

### Using a League Uri object

You can acces a `Pass` object with an already instantiated `Uri` object.

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString('http://uri.thephpleague.com:82');
$pass = $uri->pass; // $user is a League\Uri\Components\Pass object;
~~~

## Properties

The component representation, comparison and manipulation is done using the package [UriPart](/components/overview/#uri-part-interface) and the [Component](/components/overview/#component-interface) interfaces.

### Getting the decoded value of the pass 

<p class="message-notice">New in <code>version 4.2</code></p>

~~~php
<?php

public function Pass::getValue(void): string
~~~

Returns the decoded string representation of the user component

#### Example

~~~php
<?php
use League\Uri\Components\Pass;

$user = new Pass('frag%20ment');
$user->getContent(); // display 'frag%20ment'
$user->getValue(); // display 'frag ment'
~~~