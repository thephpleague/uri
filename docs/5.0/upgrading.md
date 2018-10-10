---
layout: default
title: Upgrading from 4.x to 5.x
redirect_from:
    - /upgrading/5.0/
---

# Upgrading from 4.x to 5.x

`League\Uri 5.0` is a new major version that comes with backward compatibility breaks.

This guide will help you migrate from a 4.x version to 5.0. It will only explain backward compatibility breaks, it will not present the new features ([read the documentation for that](/5.0/)).

## Installation

If you are using composer then you should update the require section of your `composer.json` file.

~~~
composer require league/uri:^5.0
~~~

This will edit (or create) your `composer.json` file.

## PHP version requirement

`League\Uri 5.0` requires a PHP version greater or equal than 7.0.0 (was previously 5.5.9).

<p class="message-warning">The package does not work on <code>HHVM</code></p>

## Magic methods

all the magic getter methods have been removed from the URI and/or URI components objects:

Before:

~~~php
<?php

use League\Uri\Schemes\Http;

$uri = Http::createFromString('http://uri.thephpleague.com/upgrading/');
$uri->path; //returns a League\Uri\Components\HierarchicalPath object
~~~

After:

~~~php
<?php

use League\Uri\Components\HierarchicalPath;
use League\Uri\Schemes\Http;

$uri = Http::createFromString('http://uri.thephpleague.com/upgrading/');
$uri->path; //triggers an exception

$path = new HierarchicalPath($uri->getPath());
 //$path is a League\Uri\Components\HierarchicalPath object
~~~

## Uri Parser

The `League\Uri\UriParser` class is renamed `League\Uri\Parser`

Before:

~~~php
<?php

use League\Uri\UriParser;

$uri = 'http://uri.thephpleague.com/5.0/';
$parser = new UriParser();
$components = $parser($uri);
~~~

After:

~~~php
<?php

use League\Uri\Parser;

$uri = 'http://uri.thephpleague.com/5.0/';
$parser = new Parser();
$components = $parser($uri);
~~~

## Uri Formatter

The `League\Uri\Formatter` class is moved under the  `League\Uri\Modifiers` namespace. The setter methods have been simplified to allow better formatting.

Before:

~~~php
<?php

use League\Uri\Formatter;

$formatter = new Formatter();
$formatter->setHostEncoding(Formatter::HOST_AS_ASCII);
$formatter->setQueryEncoding(PHP_QUERY_RFC3986);
...
~~~

After:

~~~php
<?php

use League\Uri\Modifiers\Formatter;

$formatter = new Formatter();
$formatter->setEncoding(Formatter::RFC3986_ENCODING);
...
~~~

## Query Parser

The `League\Uri\QueryParser` class is removed. All its methods have been attached to the `League\Uri\Components\Query` class.

Before:

~~~php
<?php

use League\Uri\QueryParser;

$parser = new QueryParser();
$pairs = $parser->parse('foo=bar&baz');
$query = $parser->build($pairs);
~~~

After:

~~~php
<?php

use League\Uri\Components\Query;

$pairs = Query::parse('foo=bar&baz');
$query = Query::build($pairs);
~~~

## Uri Middlewares

Starting with version 5.0, middlewares are now coded against an interface. To continue to use your old middlewares you need to use the `CallableAdapter` class adapter.

Before:

~~~php
<?php

use League\Uri\Modifiers\Pipeline;

$callable = function ($uri) {
	return $uri->withHost('thephpleague.com');
};

$pipeline = (new Pipeline())->pipe($callable);
...
~~~

After:

~~~php
<?php

use League\Uri\Modifiers\CallableAdapter;
use League\Uri\Modifiers\Pipeline;

$callable = function ($uri) {
	return $uri->withHost('thephpleague.com');
};

$pipeline = (new Pipeline())->pipe(new CallableAdapter($callable));
...
~~~

The same goes with the Pipeline constructor.

Before:

~~~php
<?php

use League\Uri\Modifiers\Pipeline;

$pipeline = new Pipeline([function ($uri) {
	return $uri->withHost('thephpleague.com');
})];
...
~~~

After:

~~~php
<?php

use League\Uri\Modifiers\Pipeline;

$pipeline = new Pipeline([new CallableAdapter(function ($uri) {
	return $uri->withHost('thephpleague.com');
}))];
...
~~~

## Uri Component interfaces

Each component used to have a specific interface located under the `League\Uri\Interfaces`. Starting with the new release, all specific interfaces for each component have been remove. The only remaining interfaces are:

- `League\Uri\Components\EncodingInterface`:  which holds the encoding constants.
- `League\Uri\Components\ComponentInterface`: The default interface for each URI component object which extends the `EncodingInterface` interface.

The following classes are removed:

- `League\Uri\Components\User`
- `League\Uri\Components\Pass`

## Host

You can no longer instantiate a Host object using the raw IP representation. Instead you are required to use the `Host::createFromIp` named constructor.

Before:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('::1');
echo $host; //display '[::1]'
~~~

After:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('::1'); //triggers an Exception
echo Host::createFromIp('::1'); //display '[::1]'
~~~


The following methods have been removed:

- `Host::hasKey`: is redundant with how `Host::getLabel` works
- `Host::isIdn`: since the the Host is always RFC3986 compliant. You can still access the RFC3987 representation of the Host using the `getContent` method.

Before:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('bébé.be');
echo $host; //display 'bébé.be'
$rfc3986_host = $host->toAscii(); // is a League\Uri\Components\Host object
echo $rfc3986_host; // display 'xn--bb-bjab.be'
~~~

After:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('bébé.be');
echo $host; // display 'xn--bb-bjab.be'
echo $host->getContent(Host::RFC3987_ENCODING); //display 'bébé.be
~~~

All methods interacting with the host label accept negative offset:

- `Host::getLabel`
- `Host::replaceLabel`
- `Host::withoutLabels`

The returned value of `Host::getLabel`, if it exists, is the RFC3987 representation.

Before:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('bébé.be');
echo $host->getLabel(-1, 'toto'); //display 'toto';
$new_host = $host->replaceLabel(-1, 'baby');
echo $new_host; // display 'bébé.be'
~~~

After:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('bébé.be');
echo $host->getLabel(-1, 'toto'); //display 'bébé';
$new_host = $host->replaceLabel(-1, 'baby');
echo $new_host; // display 'baby.be'
~~~

`Host::without` is renamed `Host::withoutLabels` and is more strict. Submitting an array that contains anything else than a integer will trigger a exception.

Before:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('thephpleague.com');
echo $host->without(['com']); //display 'thephpleague.com';
~~~

After:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('thephpleague.com');
echo $host->withoutLabels(['com']); //throw an InvalidArgumentException exception;
~~~

## HierarchicalPath

The `HierarchicalPath::createFromSegments` will always take into account the second parameter.

Before:

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = HierarchicalPath::createFromSegments(
	['', 'path', 'to', 'here'],
	HierarchicalPath::IS_RELATIVE
);
$path->isAbsolute(); //return true;
echo $path; //display '/path/to/here';
~~~

After:

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = HierarchicalPath::createFromSegments(
	['', 'path', 'to', 'here'],
	HierarchicalPath::IS_RELATIVE
);
$path->isAbsolute(); //return false;
echo $path; //display 'path/to/here';
~~~

The `HierarchicalPath::hasKey` method has been removed as it was redundant with how `HierarchicalPath::getSegment` works.

All methods interacting with the path segment offset accept negative offset:

- `Host::getSegment`
- `Host::replaceSegment`
- `Host::withoutSegments`

Before:

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = new HierarchicalPath('/path/to/the/sky');
echo $path->getSegment(-1, 'toto'); //display 'toto';
$new_path = $path->replace(-1, 'sea');
echo $new_path; // display '/path/to/the/sky'
~~~

After:

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = new HierarchicalPath('/path/to/the/sky');
echo $path->getSegment(-1, 'toto'); //display 'sky';
$new_path = $path->replaceSegment(-1, 'sea');
echo $new_path; // display '/path/to/the/sea'
~~~

`HierarchicalPath::without` is renamed `HierarchicalPath::withoutSegments` and is more strict. Submitting an array that contains anything else than a integer will trigger a exception.

Before:

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = new HierarchicalPath('/path/to/the/sky');
echo $path->without(['path']); //display '/path/to/the/sky';
~~~

After:

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = new HierarchicalPath('/path/to/the/sky');
echo $path->withoutSegments(['path']); //throw an InvalidArgumentException exception;
~~~

## Query

`Query::without` is renamed `Query::withoutPairs` and is more strict. submitted an array that contains anything else than strings will trigger a exception.

Before:

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&baz');
echo $query->without([1]); //display 'foo=bar&baz';
~~~

After:

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&baz');
echo $query->withoutPairs([1]); //throw an InvalidArgumentException exception;
~~~

The `Query::getValue` method is renamed `Query::getPair` for consistency with the other URI components object.

Before:

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&baz');
echo $query->getValue('bar', 'default'); //display 'default';
~~~

After:

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&baz');
echo $query->getPair('bar', 'default'); //display 'default';
~~~
