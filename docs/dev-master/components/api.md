---
layout: default
title: URI components
---

Uri Components API
=======

Any `League\Uri\Components` object exposes the following methods and constant defined in the `League\Uri\Interfaces\Component` interface:

~~~php
<?php

const Component::RFC3986_ENCODING = 2;
const Component::RFC3987_ENCODING = 3;
const Component::NO_ENCODING = 255;
public function Component::isDefined(void): bool
public function Component::getContent(string $enc_type = Component::RFC3986_ENCODING): mixed
public function Component::__toString(): string
public function Component::getUriComponent(void): string
public function Component::withContent(?string $content): self
~~~

## Component::isDefined

Returns `true` if the object value is not equal to `null`.


## Component::getContent

Returns the normalized and encoded version of the component.

~~~php
<?php

public function Component::getContent(string $enc_type = Component::RFC3986_ENCODING): mixed
~~~

This method return type can be:

* `null` : If the component is not defined;
* `string` : When the component is defined. This string is normalized and encoded according to the component rules;
* `int` : If it is a defined port component;

When the `$enc_type` parameter is used, the method returns a value encoded against:

- the RFC3986 rules with `Component::RFC3986_ENCODING`;
- the RFC3987 rules with `Component::RFC3987_ENCODING`;
- or no rules at all with `Component::NO_ENCODING`;

### Example

~~~php
<?php

use League\Uri\Components\Query;
use League\Uri\Components\Port;

$component = new Query();
echo $component->getContent(); //displays null

$component = new Query('');
echo $component->getContent(); //displays ''

$component = new Port(23);
echo $component->getContent(); //displays (int) 23;
~~~

## Component::__toString

Returns the normalized and RFC3986 encoded string version of the component.

~~~php
<?php

public Component::__toString(void): string
~~~

### Example

~~~php
<?php

use League\Uri\Components\Scheme;
use League\Uri\Components\UserInfo;
use League\Uri\Components\HierarchicalPath;

$scheme = new Scheme('http');
echo $scheme->__toString(); //displays 'http'

$userinfo = new UserInfo('john');
echo $userinfo->__toString(); //displays 'john'

$path = new HierarchicalPath('/toto le heros/file.xml');
echo $path->__toString(); //displays '/toto%20le%20heros/file.xml'
~~~

<p class="message-notice">Normalization and encoding are specific to the component.</p>

## Component::getUriComponent

Returns the string representation of the normalized and RFC3986 encoded URI part with its optional delimiter if required.

~~~php
<?php

public UriPart::getUriComponent(void): string
~~~

### Example

~~~php
<?php

use League\Uri\Components\Scheme;

$scheme = new Scheme('HtTp');
echo $scheme->getUriComponent(); //display 'http:'

$userinfo = new UserInfo('john');
echo $userinfo->getUriComponent(); //displays 'john@'
~~~

<p class="message-notice">Normalization, encoding and delimiters are specific to the URI part.</p>

## Component::withContent

This method accepts any string in no particular encoding but will try to normalize the string to be RFC3986 compliant.

~~~php
<?php

use League\Uri\Components\Query;
use League\Uri\Components\Host;

$component = new Query();
echo $component->withContent('')->getContent(); //returns ''

$component = new Query('');
$component->withContent(null)->getContent(); //returns null

$component = new Host('thephpleague.com');
echo $component->withContent('bébé.be'); //displays 'xn--bb-bjab.be';
~~~

Creating new objects
--------

To instantiate a new component object you can use the default constructor as follow:

~~~php
<?php
public Component::__construct(string $content = null): void
~~~
