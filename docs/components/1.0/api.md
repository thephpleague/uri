---
layout: default
title: URI components
redirect_from:
    - /5.0/components/api/
---

Components common API
=======

## Accessing URI component representation

Once instantiated, all URI component objects expose the methods and constants defined in the `ComponentInterface` interface.

This interface is used to normalized URI component representation while taking into account each component specificity.

~~~php
<?php

interface ComponentInterface
{
	const NO_ENCODING = 0;
	const RFC1738_ENCODING = 1;
	const RFC3986_ENCODING = 2;
	const RFC3987_ENCODING = 3;

	public function __toString(void): string
	public function getContent(int $enc_type = self::RFC3986_ENCODING): mixed
	public function getUriComponent(void): string
	public function isEmpty(void): bool
	public function isNull(void): bool
}
~~~

Which will lead to the following results:

~~~php
<?php

use League\Uri\Components\Scheme;
use League\Uri\Components\UserInfo,
use League\Uri\Components\HierarchicalPath;
use League\Uri\Components\Host;
use League\Uri\Components\Query;
use League\Uri\Components\Path;
use League\Uri\Components\Port;


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

<p class="message-info">Normalization and encoding are component specific.</p>
<p class="message-info">By default, <code>$enc_type</code> equals <code>ComponentInterface::RFC3986_ENCODING</code></p>

## Modifying URI component object

All URI component objects can be modified with the `ComponentInterface::withContent` method. This method accepts:

- the `null` value
- a string in no particular encoding.

and returns a new instance with the modified content.

<p class="message-info">submitted string is normalized to be RFC3986 compliant.</p>

~~~php
<?php

use League\Uri\Components\Host;
use League\Uri\Components\Query;

$query = new Query();
$new_query = $query->withContent('');
echo $query->getContent(); //returns null
echo $new_query->getContent(); //returns ''

$host = new Host('thephpleague.com');
$new_host = $host->withContent('bébé.be');
echo $new_host->getContent(); //displays 'xn--bb-bjab.be';
echo $new_host->getContent(Host::RFC3987_ENCODING); //displays 'bébé.be';
~~~

<p class="message-warning">If the submitted value is not valid an <code>League\Uri\Components\Exception</code> exception is thrown.</p>

List of URI component objects
--------

The following URI component objects are defined (order alphabetically):

- [DataPath](/components/1.0/data/) : the Data Path component [RFC 2397](https://tools.ietf.org/html/rfc2397)
- [HierarchicalPath](/components/1.0/hierarchicalpath/) : the hierarchical Path component [RFC 3986](https://tools.ietf.org/html/rfc3986)
- [Host](/components/1.0/host/) : the Host component
- [Fragment](/components/1.0/fragment/) : the Fragment component
- [Path](/components/1.0/path/) : the generic Path component
- [Port](/components/1.0/hierarchicalpath/) : the Port component
- [Query](/components/1.0/query/) : the Query component
- [Scheme](/components/1.0/scheme/) : the Scheme component
- [UserInfo](/components/1.0/userinfo/) : the User Info component

Some URI component objects expose more methods to enable better manipulations.