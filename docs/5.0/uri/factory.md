---
layout: default
title: Uri Object API
---

URI Factory
=======

<p class="message-notice">available since version <code>1.1.0</code></p>

Most of the time you receive a URI string without any information about it scheme to ease URI object creation you can use the `League\Uri\Factory` object.

## Using the factory class.

~~~php
<?php

use League\Uri\Interfaces\Uri as LeagueUriInterface;
use League\Uri;
use Psr\Http\Message\UriInterface;

class Factory {
    public function __construct($map = []);
    public function create(
        string $uri,
        LeagueUriInterface|UriInterface|string ?$base_uri = null
    ): LeagueUriInterface|UriInterface
}
~~~

### The constructor

The constructor takes a iterable structure which maps a URI scheme to a specific class. This class must either implements the PSR UriInterface of the League Uri Interface. By default the factory uses all the class found in the league URI scheme package.

~~~php
<?php

use League\Uri\Factory;
use Zend\Diactors\Uri;

$http_uri = 'http://thephpleague.com';
$https_uri = 'https://thephpleague.com';

$factory = new Factory();
$uri = $factory->create($http_uri);  // will return a Uri\Http object
$uri = $factory->create($https_uri); // will return a Uri\Http object

$factorybis = new Factory(['http' => Uri::class]);
$uri = $factorybis->create($http_uri); // will return a Zend\Diactors\Uri object
$uri = $factorybis->create($https_uri); // will return a Uri\Http object
~~~

### The create method


The `create` method takes an optional base URI. This base URI can be:

- a League URI object
- a PSR UriInterface object
- a string

The base URI is required to be a absolute URI otherwise an exception will be thrown. When a base URI is given the URI is resolved against that base URI just like a browser would for a relative URI.

~~~php
<?php

use League\Uri\Factory;

$factory = new Factory();
$uri = $factory->create('./p#~toto', 'http://thephpleague.com/uri/5.0/uri/');
echo $uri; //returns 'http://thephpleague.com/uri/5.0/uri/p#~toto'
~~~

- If the given URI shares the same scheme as the base URI or does not have a scheme, the return URI will be created using the baseURI URI object.

~~~php
<?php

use League\Uri\Factory;
use Zend\Diactors\Uri;

$zendUri = new Uri('http://thephpleague.com/uri/5.0/uri/');
$factory = new Factory();
$http_uri = $factory->create('./p#~toto', $zendUri); // will return a Zend\Diactors\Uri object
~~~

- If the given URI does not share the same scheme as the base URI but has a scheme supported by the factory, the factory will the scheme specific class to try to create the URI object

~~~php
<?php

use League\Uri\Factory;

$factory = new Factory();
$ftp_uri = $factorybis->create(
    'ftp://example.com/file.md',
    'http://thephpleague.com/uri/5.0/uri/'
); // will return a Uri\Ftp object
~~~

- If the scheme is not recognzied the factory will use the [generic URI](/5.0/uri/schemes/uri/) class object

~~~php
<?php

use League\Uri\Factory;
use League\Uri\Http;

$currentUri = Http::createFromServer($_SERVER);

$factory = new Factory();
$uri = $factorybis->create('mailto:info@thephpleague.com', $currentUri);
// will return a generic League\Uri\Uri object
~~~

In all cases if an URI object can not be created a exception will be thrown.

## Using the create function.

If the currently supported schemes are enough for you, the package comes bundles with the `League\Uri\create` function to ease the `Factoy` usage.

~~~php
<?php

use League\Uri;
use Zend\Diactors\Uri as ZendPsrUri;

$uri = Uri\create('./p#~toto', 'http://thephpleague.com/uri/5.0/uri/');
echo $uri; //returns 'http://thephpleague.com/uri/5.0/uri/p#~toto'
		   // which is a Uri\Http object

$zendUri = new ZendPsrUri('http://thephpleague.com/uri/5.0/uri/');

$uri = Uri\create('./p#~toto', $zendUri);
echo $uri; //returns 'http://thephpleague.com/uri/5.0/uri/p#~toto'
		   // which is a Zend\Diactors\Uri object
~~~