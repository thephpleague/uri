---
layout: default
title: The User component
---

# The User component

The library provides a `League\Uri\Components\User` class to ease user manipulation.

## Instantiation

A new `League\Uri\Components\User` object can be instantiated using its default constructor.

~~~php
<?php

use League\Uri\Components\User;

$component = new User('ftp');
echo $component; //display 'ftp'

$empty_component = new User();
echo $empty_component; //display ''
~~~

The scheme component constructor accepts:

- a valid string according to their component validation rules as explain in RFC3986;
- the `null` value;

<p class="message-warning">If the submitted value is not a valid an <code>InvalidArgumentException</code> exception is thrown.</p>

On instantiation the User is normalized using RFC3986 rules.

### Using a League Uri object

You can acces a `League\Uri\Components\User` object with an already instantiated League Uri object.

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri  = HttpUri::createFromString('http://uri.thephpleague.com:82');
$component = $uri->userInfo->user; // $user is a League\Uri\Components\User object;
~~~

## User properties

### UriPart representations

User representations is done using the `UriPart` interface methods:

~~~php
<?php

use League\Uri\Components\User;

$component = new User('HtTp');
$component->getContent();      //return 'http'
$component->__toString();      //return 'http'
$component->getUriComponent(); //return 'http'
~~~

### User::getValue

<p class="message-notice">New since <code>version 4.2</code></p>

Returns the decoded value of a User component

~~~php
<?php

public User::getValue(void): string
~~~

#### Example

~~~php
<?php

use League\Uri\Components\User;

$component = new User('%E2%82%AC');
echo $component->getUriComponent(); //displays '%E2%82%AC'
echo $component->getValue(); //displays '€'
~~~


To [compare](/components/overview/#components-comparison) or [manipulate](/components/overview/#components-modification) the port object you should refer to the component overview section.