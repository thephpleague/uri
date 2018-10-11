---
layout: default
title: The Pass component
    - /4.0/components/pass/
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

#### Example

~~~php
<?php

use League\Uri\Components\Pass;

$user = new Pass('jo@hn');
echo $user->getContent();      //display 'jo%40hn'
echo $user;                    //display 'jo%40hn'
echo $user->getUriComponent(); //display 'jo%40hn'

$user = new Pass();
echo $user->getContent();      //display null
echo $user;                    //display ''
echo $user->getUriComponent(); //display ''
~~~

<p class="message-warning">If the submitted value is not a valid an <code>InvalidArgumentException</code> exception is thrown.</p>

<p class="message-info">On instantiation the submitted string is normalized using RFC3986 rules.</p>

### Using a League Uri object

You can access a `Pass` object with an already instantiated `Uri` object.

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString('http://uri.thephpleague.com:82');
$pass = $uri->pass; // $user is a League\Uri\Components\Pass object;
~~~

## Properties and Methods

The component representation, comparison and manipulation is done using the package [UriPart](/uri/4.0/components/overview/#uri-part-interface) and the [Component](/uri/4.0/components/overview/#uri-component-interface) interfaces methods.

### Pass::getDecoded

<p class="message-notice">New in <code>version 4.2</code></p>

~~~php
<?php

public function Pass::getDecoded(void): null|string
~~~

Returns the decoded value of the component `getContent` method

#### Example

~~~php
<?php
use League\Uri\Components\Pass;

$user = new Pass('frag%20ment');
$user->getContent(); // display 'frag%40ment'
$user->getDecoded();   // display 'frag@ment'
~~~