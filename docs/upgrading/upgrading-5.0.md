---
layout: default
title: Upgrading from 4.x to 5.x
permalink: upgrading/5.0/
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

`League\Uri 5.0` requires a PHP version greater or equal than 5.6.4 (was previously 5.5.9).

<p class="message-warning">The package does not work on <code>HHVM</code></p>

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
$parser = new UriParser();
$components = $parser($uri);
~~~

## Uri Formatter

The `League\Uri\Formatter` class is moved under the  `League\Uri\Modifiers` namespace. The setter methods have been simplified and renamed to allow returning RFC3987 encoded URI or URI component more easily.

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

use League\Uri\Schemes\Http;

$uri = Http::createFromString('http://uri.thephpleague.com/upgrading/');
$uri->path; //triggers an exception
~~~

## Uri components interfaces

The interfaces use to be located under the `League\Uri\Interfaces`. Now the interfaces are under the `League\Uri\Components`. All the specific interfaces for each component have been remove for simplicity. The only remaining interfaces are:

- `League\Uri\Components\ComponentInterface`: The default interface for each URI component object
- `League\Uri\Components\PathInterface`: The default interface for the path component. It extends the `ComponentInterface`.

The following classes are removed:

- `League\Uri\Components\User`
- `League\Uri\Components\Pass`

## Host

The following methods have been removed:

- `Host::hasKey`: is redundant with how `Host::getLabel` works
- `Host::isIdn`: since the the Host is always RFC3986 compliant. You can still access the RFC3987 representation of the Host using the `getContent` method.

Before:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('bébé.be');
echo $host; //display 'bébé.be
$rfc3986_host = $host->toAscii();
echp $rfc3986_host; // display 'xn--bb-bjab.be'
~~~

After:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('bébé.be');
echo $host; // display 'xn--bb-bjab.be'
echo $host->getContent(Host::RFC3987_ENCODING); //display 'bébé.be
~~~

The `Host::getLabel` and `Host::replace` accept negative offset.

The returned value of `Host::getLabel`, if it exists, is the RFC3987 representation.

Before:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('bébé.be');
echo $host->getLabel(-1, 'toto'); //display 'toto';
$new_host = $host->replace(-1, 'baby');
echo $new_host; // display 'bébé.be'
~~~

After:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('bébé.be');
echo $host->getLabel(-1, 'toto'); //display 'bébé';
$new_host = $host->replace(-1, 'baby');
echo $new_host; // display 'baby.be'
~~~

`Host::without` is more strict. Submitting an array that contains anything else than a integer will trigger a exception.

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

use League\Uri\Components\HierarchicalPath;

$host = new Host('thephpleague.com');
echo $host->without(['com']); //throw an InvalidArgumentException exception;
~~~

## HierarchicalPath

The `HierarchicalPath::hasKey` method has been removed as it was redundant with how `HierarchicalPath::getSegment` works.

`HierarchicalPath::getSegment` and `HierarchicalPath::replace` now accept negative offset.

Before:

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = new HierarchicalPath('/path/to/the/sky');
echo $path->getLabel(-1, 'toto'); //display 'toto';
$new_path = $path->replace(-1, 'sea');
echo $new_path; // display '/path/to/the/sky'
~~~

After:

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = new HierarchicalPath('/path/to/the/sky');
echo $path->getLabel(-1, 'toto'); //display 'sky';
$new_path = $path->replace(-1, 'sea');
echo $new_path; // display '/path/to/the/sea'
~~~

`HierarchicalPath::without` is more strict. Submitting an array that contains anything else than a integer will trigger a exception.

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
echo $path->without(['path']); //throw an InvalidArgumentException exception;
~~~

## Query

The `Query::hasKey` method has been removed as it was redundant with how `Query::getValue` works.

The `Query::merge` method no longer accepts another `Query` object as a valid parameter you need to supply a string.

Before:

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&baz');
echo $query->merge(new Query('baz=foo')); //display 'foo=bar&baz=foo';
~~~

After:

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&baz');
echo $query->merge(new Query('baz=foo')); //throw an InvalidArgumentException exception;
~~~

`Query::without` is more strict. submitted an array that contains anything else than a string will trigger a exception.

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
echo $query->without([1]); //throw an InvalidArgumentException exception;
~~~