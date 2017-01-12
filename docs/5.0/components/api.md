---
layout: default
title: URI components
---

Components common API
=======

## Creating new URI component object

All URI object can be instantiate using the default constructor by providing:

~~~php
<?php
public Component::__construct($content = null): void
~~~

the `$content` argument can be `null`, a string **or** an integer (in case of the `Port` object).

<p class="message-notice">submitted string is normalized to be RFC3986 compliant.</p>

## Accessing URI component representation

Once instantiated, all URI component objects expose the methods and constant defined in the `ComponentInterface` interface. This interface is used to normalized URI component representation while taking into account each component specificity.

~~~php
<?php

const ComponentInterface::NO_ENCODING = 0;
const ComponentInterface::RFC1738_ENCODING = 1;
const ComponentInterface::RFC3986_ENCODING = 2;
const ComponentInterface::RFC3987_ENCODING = 3;
public function ComponentInterface::isNull(void): bool
public function ComponentInterface::isEmpty(void): bool
public function ComponentInterface::getContent(int $enc_type = self::RFC3986_ENCODING): mixed
public function ComponentInterface::__toString(void): string
public function ComponentInterface::getUriComponent(void): string
~~~

Which will lead to the following results:

~~~php
<?php

use League\Uri\Components\{
	Scheme,
	UserInfo,
	HierarchicalPath,
	Host,
	Query,
	Path,
	Port
};

$scheme = new Scheme('HtTp');
echo $scheme->__toString(); //displays 'http'
echo $scheme->getUriComponent(); //display 'http:'

$userinfo = new UserInfo('john');
echo $userinfo->__toString(); //displays 'john'
echo $userinfo->getUriComponent(); //displays 'john@'

$path = new HierarchicalPath('/toto le heros/file.xml');
echo $path->getContent(HierarchicalPath::NO_ENCODING); //displays '/toto le heros/file.xml'
echo $path->__toString(); //displays '/toto%20le%20heros/file.xml'

$host = new Host('bébé.be');
echo $host; //displays 'xn--bb-bjab.be'
echo $host->getContent(Host::RFC3987_ENCODING); //displays 'bébé.be'

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


- `ComponentInterface::__toString` returns the normalized and RFC3986 encoded string version of the component.
- `ComponentInterface::getUriComponent` returns the same output as `ComponentInterface::__toString` with the component optional delimiter.

For a more generalized representation you must use the `ComponentInterface::getContent` method. This method returns type can be:

* `null` : If `ComponentInterface::isNull` returns `true`;
* `string` : If `ComponentInterface::isNull` returns `false`;
* `int`: For a defined URI component Port object;

 The string is normalized and encoded according to the component rules and the optional `$enc_type` parameter. The `$enc_type` parameter can take on of the following values:

- `ComponentInterface::RFC1738_ENCODING` encodes the component using RFC1738 rules;
- `ComponentInterface::RFC3986_ENCODING` encodes the component using RFC3986 rules;
- `ComponentInterface::RFC3987_ENCODING` encodes the component using RFC3987 rules;
- `ComponentInterface::NO_ENCODING` no encoding is done;

<p class="message-notice">Normalization and encoding are component specific.</p>
<p class="message-notice">By default, <code>$enc_type</code> equals <code>ComponentInterface::RFC3986_ENCODING</code></p>

## Modifying URI component object

All URI component objects can be modified with the `ComponentInterface::withContent` method. This method accepts:

- the `null` value
- a string in no particular encoding.

and returns a new instance with the modified content.

<p class="message-notice">submitted string is normalized to be RFC3986 compliant.</p>

~~~php
<?php

use League\Uri\Components\{
	Query,
	Host
};

$query = new Query();
$new_query = $query->withContent('');
echo $query->getContent(); //returns null
echo $new_query->getContent(); //returns ''

$host = new Host('thephpleague.com');
$new_host = $host->withContent('bébé.be');
echo $new_host->getContent(); //displays 'xn--bb-bjab.be';
echo $new_host->getContent(Host::RFC3987_ENCODING); //displays 'bébé.be';
~~~

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