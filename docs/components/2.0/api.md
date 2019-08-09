---
layout: default
title: URI components
---

Components common API
=======

## Instantiation

Each URI component objects can be instantiated from a URI object using the `createFromUri` named constructor.

~~~php
public static function UriComponent::createFromUri($uri): UriComponentInterface;
~~~

This method accepts a single `$uri` parameter which can be an object implementing a:

- League `League\Uri\Contracts\UriInterface` or
- PSR-7 `Psr\Http\Message\UriInterface`

~~~php
use League\Uri\Components\Host;
use League\Uri\Components\Path;
use League\Uri\Components\Port;
use League\Uri\Components\Query;
use League\Uri\Uri;

$uri = Uri::createFromString('http://example.com?q=value#fragment');
$host = Host::createFromUri($uri)->getContent();   //displays 'example.com'
$query = Query::createFromUri($uri)->getContent(); //displays 'q=value'
$port = Port::createFromUri($uri)->getContent();   //displays null
$path = Path::createFromUri($uri)->getContent();   //displays ''
~~~

<p class="message-info">Depending on the URI component the default constructor and other named constructors can be use for instantiation.</p> 

## Accessing URI component representation

Once instantiated, all URI component objects expose the following methods.

~~~php
public function UriComponent::__toString(): string;
public function UriComponent::jsonSerialize(): ?string;
public function UriComponent::getContent(): ?string;
public function UriComponent::getUriComponent(): string;
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

$query = Query::createFromRFC6986();
echo $query; //displays ''
echo $query->getContent(); //displays null

$port = new Port(23);
echo $port->getContent(); //displays '23';
~~~

- `__toString` returns the normalized and RFC3986 encoded string version of the component.
- `getUriComponent` returns the same output as `__toString` with the component optional delimiter.
- `jsonSerialize` returns the normalized and RFC1738 encoded string version of the component for better interoperability with JavaScript URL standard.

<p class="message-info">For a more generalized representation you must use the <code>getContent</code> method. If the component is undefined, the method returns <code>null</code>.</p>

<p class="message-notice">Normalization and encoding are component specific.</p>

## Modifying URI component object


All URI component objects can be modified with the `withContent` method. This method returns a new instance with the modified content.

~~~php
public function UriComponent::withContent(?string $content): static;
~~~

<p class="message-info">submitted input is normalized to be RFC3986 compliant.</p>

~~~php
$query = Query::createFromRFC3986();
$newQuery = $query->withContent('');
echo $query->getContent(); //returns null
echo $newQuery->getContent(); //returns ''

$host = new Host('thephpleague.com');
$newHost = $host->withContent('bébé.be');
echo $newHost->getContent(); //displays 'xn--bb-bjab.be';
echo $newHost->toUnicode(); //displays 'bébé.be';
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

<p class="message-info">In addition to the common API, the classes expose specific methods to improve URI component manipulation.</p>
