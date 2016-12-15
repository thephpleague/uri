---
layout: default
title: URI components
---

Uri Components API
=======

All URI component objects expose the following methods and constant defined in the `League\Uri\Components\ComponentInterface` interface:

~~~php
<?php

const ComponentInterface::RFC3986_ENCODING = 2;
const ComponentInterface::RFC3987_ENCODING = 3;
const ComponentInterface::NO_ENCODING = 255;
public function ComponentInterface::isNull(void): bool
public function ComponentInterface::isEmpty(void): bool
public function ComponentInterface::getContent(string $enc_type = ComponentInterface::RFC3986_ENCODING): mixed
public function ComponentInterface::__toString(): string
public function ComponentInterface::getUriComponent(void): string
public function ComponentInterface::withContent(?string $content): self
~~~

## ComponentInterface::isNull

Returns `true` if the component value is equal to `null`.

## ComponentInterface::isEmpty

Returns `true` if the component value is equal to `null` or represents the empty string.

## ComponentInterface::getContent

Returns an encoded version of the component or `null`.

~~~php
<?php

public function ComponentInterface::getContent(string $enc_type = ComponentInterface::RFC3986_ENCODING): mixed
~~~

This method returns type can be:

* `null` : If the component is not defined;
* `string` : When the component is defined. This string is normalized and encoded according to the component rules;
* `int` : If it is a defined port component;

When the `$enc_type` parameter is used, and a string is returned, the value can be encoded against:

- the RFC3986 rules with `ComponentInterface::RFC3986_ENCODING`;
- the RFC3987 rules with `ComponentInterface::RFC3987_ENCODING`;
- or no rules at all with `ComponentInterface::NO_ENCODING`;

### Example

~~~php
<?php

use League\Uri\Components\Query;
use League\Uri\Components\Path;
use League\Uri\Components\Port;

$query = new Query();
$query->isNull(); //returns true
$query->isEmpty(); //returns true
echo $query->getContent(); //displays null

$path = new Path('');
$path->isNull(); //returns false
$path->isEmpty(); //returns true
echo $path->getContent(); //displays ''

$port = new Port(23);
$port->isNull(); //returns false
$port->isEmpty(); //returns false
echo $port->getContent(); //displays (int) 23;
~~~

## ComponentInterface::__toString

Returns the normalized and RFC3986 encoded string version of the component.

~~~php
<?php

public ComponentInterface::__toString(void): string
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

<p class="message-notice">Normalization and encoding are component specific.</p>

## ComponentInterface::getUriComponent

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

<p class="message-notice">Normalization, encoding and delimiters are component specific.</p>

## ComponentInterface::withContent

This method accepts:

- the `null` value
- a string in no particular encoding.

Returns a new instance with the modified content.

<p class="message-notice">submitted string is normalized to be RFC3986 compliant.</p>

~~~php
<?php

use League\Uri\Components\Query;
use League\Uri\Components\Host;

$query = new Query();
echo $query->withContent('')->getContent(); //returns ''

$query = new Query('');
$query->withContent(null)->getContent(); //returns null

$host = new Host('thephpleague.com');
echo $host->withContent('bébé.be')->getContent(); //displays 'xn--bb-bjab.be';
echo $host->withContent('bébé.be')->getContent(Host::RFC3987_ENCODING); //displays 'bébé.be';
~~~

Creating new objects
--------

All URI object can be instantiate using the default constructor by providing a string or the `null` value.

~~~php
<?php
public Component::__construct(string $content = null): void
~~~

<p class="message-notice">submitted string is normalized to be RFC3986 compliant.</p>


List of URI component objects
--------

The following URI component objects are defined (order alphabetically):

- [DataPath](/5.0/components/data/) : the Data Path component [RFC 2397](https://tools.ietf.org/html/rfc2397)
- [HierarchicalPath](/5.0/components/hierarchicalpath/) : the hierarchical Path component [RFC 3986](https://tools.ietf.org/html/rfc3986)
- [Host](/5.0/components/host/) : the Host component
- [Fragment](/5.0/components/fragment/) : the Fragment component
- [Path](/5.0/components/path/) : the generic Path component
- [Port](/5.0/components/hierarchicalpath/) : the Port component
- [Query](/5.0/components/query/) : the Query component
- [Scheme](/5.0/components/scheme/) : the Scheme component
- [UserInfo](/5.0/components/userinfo/) : the User Info component

Some URI component objects expose more methods to enable better manipulations.