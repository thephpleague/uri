---
layout: default
title: URI components
---

Components common API
=======

## Instantiation

Each URI component objects can be instantiated from a URI object using the `createFromUri` named constructor.
This method accepts the League or the PSR-7 `UriInterface`.

~~~php
use League\Uri\Components\Query;
use function GuzzleHttp\uri_for;

$uri = uri_for('http://example.com?q=value#fragme`nt');
$query = Query::createFromUri($uri);
$query->getContent(); //displays 'q=value';
$query->get('q'); //returns 'value';
~~~

Of course depending on the URI components the defautl constructor or other named constructor can be used to instantiate  the object. 

## Accessing URI component representation

Once instantiated, all URI component objects expose the methods and constants defined in the `UriComponentInterface` interface.

This interface is used to normalized URI component representation while taking into account each component specificity.

~~~php
<?php

interface UriComponentInterface
{
	public function __toString(void): string;
	public function getContent(void): ?string;
	public function getUriComponent(void): string;
	public function withContent(?string $content): static;
}
~~~

Which will lead to the following results:

~~~php
$scheme = new Scheme('HtTp');
echo $scheme->__toString(); //displays 'http'
echo $scheme->getUriComponent(); //display 'http:'

$userinfo = new UserInfo('john');
echo $userinfo->__toString(); //displays 'john'
echo $userinfo->getUriComponent(); //displays 'john@'

$host = new Host('bébé.be');
echo $host; //displays 'xn--bb-bjab.be'
echo $host->getContent(); //displays 'xn--bb-bjab.be'

$query = new Query();
echo $query; //displays ''
echo $query->getContent(); //displays null

$port = new Port(23);
echo $port->getContent(); //displays '23';
~~~

- `ComponentInterface::__toString` returns the normalized and RFC3986 encoded string version of the component.
- `ComponentInterface::getUriComponent` returns the same output as `ComponentInterface::__toString` with the component optional delimiter.

For a more generalized representation you must use the `ComponentInterface::getContent` method. This method returns type can be `null` or  `string`.

<p class="message-info">Normalization and encoding are component specific.</p>

## Modifying URI component object

All URI component objects can be modified with the `ComponentInterface::withContent` method. This method returns a new instance with the modified content.

<p class="message-info">submitted input is normalized to be RFC3986 compliant.</p>

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
echo $new_host->toUnicode(); //displays 'bébé.be';
~~~

<p class="message-warning">If the submitted value is not valid an <code>League\Uri\Exceptions\UriException</code> exception is thrown.</p>

List of URI component objects
--------

The following URI component objects are defined (order alphabetically):

- [Authority](/components/2.0/authority/) : the Data Path component
- [DataPath](/components/2.0/path/data/) : the Data Path component
- [Domain](/components/2.0/host/domain/) : the Host component
- [Fragment](/components/2.0/fragment/) : the Fragment component
- [HierarchicalPath](/components/2.0/path/segmented/) : the Segmented Path component
- [Host](/components/2.0/host/) : the Host component
- [Path](/components/2.0/path/) : the generic Path component
- [Port](/components/2.0/port/) : the Port component
- [Query](/components/2.0/query/) : the Query component
- [Scheme](/components/2.0/scheme/) : the Scheme component
- [UserInfo](/components/2.0/userinfo/) : the User Info component
