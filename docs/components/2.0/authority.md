---
layout: default
title: Authority URI part Object API
---

The Authority part
=======

The `League\Uri\Components\Authority` class ease URI authority part creation and manipulation. This object exposes:
                                       
- the [package common API](/components/2.0/api/), 

but also provide specific methods to work with a URI authority part.

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Exceptions\SyntaxError</code> exception is thrown.</p>

## Instantiation

### Using the default constructor

<p class="message-warning">The default constructor is deprecated starting with version <code>2.3.0</code>. It should be replaced by one of the several new named constructors.</p>

~~~php
<?php
public Authority::__construct($authority = null): void
~~~

<p class="message-notice">submitted string is normalized to be <code>RFC3986</code> compliant.</p>

### Using a string

~~~php
<?php

use League\Uri\Components\Authority;

$authority = Authority::createFromString('user:pass@example.com:42');
$authority->getContent(); //returns 'user:pass@example.com:42'
~~~

<p class="message-notice">if no string is given a instance is returns using the empty string.</p>

### Using Uri components
 
~~~php
<?php

use League\Uri\Components\Authority;
use League\Uri\UriString;

$authority = Authority::createFromComponents(
	UriString::parse("http://user:pass@example.com:42/5.0/uri/api")
);
$authority->getContent(); //returns 'user:pass@example.com:42'
~~~

<p class="message-warning">If you supply your own hash to <code>createFromComponents</code>, you are responsible for providing well parsed components without their URI delimiters.</p>

### Using an URI object

The URI must implements League `UriInterface` or PSR-7  `UriInterface`.

~~~php
<?php

use Laminas\Diactoros\Uri as LaminasUri;
use League\Uri\Components\Authority;

$psr7Uri = new LaminasUri("http://www.example.com/path/to/the/sky");

$authority = Authority::createFromUri($psr7Uri);
$authority->getPort(); //return null;
~~~

### Using null

~~~php
<?php

use League\Uri\Components\Authority;

$authority = Authority::createFromNull();
$authority->getContent(); //return null;
~~~

Authority validation
-------

A `League\Uri\Contracts\UriException` exception is triggered if an invalid Authority value is given.

~~~php
$uri = Authority::createFromString(':80');
// throws a League\Uri\Exceptions\SyntaxError
// because the URI string is invalid
~~~

Accessing properties
-------

The Authority object exposes the following specific methods.

~~~php
public function Authority::getUserInfo(void): ?string
public function Authority::getHost(void): ?string
public function Authority::getPort(void): ?int
~~~

You can access the authority string, its individual parts and components using their respective getter methods. This lead to the following result for a simple HTTP URI:

~~~php
$uri = Authority::createFromString("foo:bar@www.example.com:81");
echo $uri->getUserInfo();  //displays "foo:bar"
echo $uri->getHost();      //displays "www.example.com"
echo $uri->getPort();      //displays 81 as an integer
echo $uri;
//displays "foo:bar@www.example.com:81"
echo json_encode($uri);
//displays "foo:bar@www.example.com:81"
~~~

Modifying properties
-------

To replace one of the URI component you can use the modifying methods exposed by all URI object. If the modifications do not alter the current object, it is returned as is, otherwise, a new modified object is returned.

<p class="message-notice">Any modification method can trigger a <code>League\Uri\Contracts\UriException</code> exception if the resulting URI is not valid. Just like with the instantiation methods, validition is scheme dependant.</p>

~~~php
<?php

public function Authority::withUserInfo(?string $user, ?string $password = null): self
public function Authority::withHost(?string $host): self
public function Authority::withPort(?int $port): self
~~~

Since All URI object are immutable you can chain each modifying methods to simplify URI creation and/or modification.

~~~php
$uri = Authority::createFromString("thephpleague.com")
    ->withUserInfo("foo", "bar")
    ->withHost("www.example.com")
    ->withPort(81);

echo $uri->getUriComponent(); //displays "//foo:bar@www.example.com:81"
~~~

Normalization
-------

Out of the box the package normalizes the URI part according to the non destructive rules of RFC3986.

These non destructive rules are:

- scheme and host components are lowercased;
- the host is converted to its ascii representation using punycode if needed

~~~php
$uri = new Uri("www.ExAmPLE.com:80");
echo $uri; //displays www.example.com:80
~~~

<p class="message-info">Host conversion depends on the presence of the <code>ext-intl</code> extension. Otherwise the code will trigger a <code>IdnSupportMissing</code> exception</p>
