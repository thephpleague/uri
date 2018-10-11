---
layout: default
title: The URI Formatter
redirect_from:
    - /4.0/uri/services/formatter/
---

# The Formatter

The Formatter service class helps you format your URI according to your output.

## Formatting URI and URI parts

~~~php
<?php

public Formatter::format(mixed $input): string
public Formatter::__invoke(mixed $input): string
~~~~~~

The method which process the formatting action is `Formatter::format`.

<p class="message-notice">Since <code>version 4.1</code>: <code>Formatter::__invoke</code> is an alias of <code>Formatter::format</code>.</p>

This methods expect one of the following input:

- an Uri object (which implements PSR-7 `UriInterface` or the `League\Interfaces\Uri` interface);
- a `League\Interfaces\UriPart` Interface.

<p class="message-warning">The returned string <strong>MAY</strong> no longer be a valid URI</p>

## Formatter Properties

### Host encoding strategy

~~~php
<?php

public Formatter::setHostEncoding(int $format): void
public Formatter::getHostEncoding(void): int
~~~~~~

<p class="message-warning">The <code>getHostEncoding</code> method is deprecated since <code>version 4.1</code>  and will be removed in the next major release</p>

A host can be output as encoded in ascii or in unicode. By default the formatter encode the host in unicode. To set the encoding you need to specify one of the predefined constant:

- `Formatter::HOST_AS_UNICODE` to set the host encoding to IDN;
- `Formatter::HOST_AS_ASCII`   to set the host encoding to ascii;

~~~php
<?php

use League\Uri\Formatter;
use League\Uri\Components\Host;

$formatter = new Formatter();
$formatter->setHostEncoding(Formatter::HOST_AS_UNICODE);
echo $formatter->getHostEncoding(); //display the value of Formatter::HOST_AS_ASCII

$host = new Host('рф.ru');
echo $host;                     //displays 'xn--p1ai.ru'
echo $formatter->format($host); //displays 'рф.ru'
echo $formatter($host);         //displays 'рф.ru'
~~~

### Query encoding strategy

~~~php
<?php

public Formatter::setQueryEncoding(int $encoding): void
public Formatter::getQueryEncoding(void): int
~~~~~~

<p class="message-warning">The <code>getQueryEncoding</code> method is deprecated since <code>version 4.1</code>  and will be removed in the next major release</p>

A `League\Uri\Components\Query` object is by default encoded by following RFC 3986. If you need to change this encoding to the old RFC 1738, you just need to update the query encoding as shown below using the following predefined constant:

- `PHP_QUERY_RFC3986` to set the query encoding as per RFC 3986;
- `PHP_QUERY_RFC1738` to set the query encoding as per RFC 1738;

~~~php
<?php

use League\Uri\Formatter;
use League\Uri\Components\Query;

$formatter = new Formatter();
$formatter->setQueryEncoding(PHP_QUERY_RFC1738);
echo $formatter->getQueryEncoding(); //display the value of PHP_QUERY_RFC1738;

$query = Query::createFromArray(['foo' => 'ba r', "baz" => "bar"]);
echo $query; //displays foo=ba%20&baz=bar
echo $formatter->format($query); //displays foo=ba+r&baz=bar
echo $formatter($query);         //displays foo=ba+r&baz=bar
~~~

### Query separator strategy

~~~php
<?php

public Formatter::setQuerySeparator(string $separator): void
public Formatter::getQuerySeparator(void): string
~~~

<p class="message-warning">The <code>getQuerySeparator</code> method is deprecated since <code>version 4.1</code>  and will be removed in the next major release</p>

~~~php
<?php

use League\Uri\Formatter;
use League\Uri\Components\Query;

$formatter = new Formatter();
$formatter->setQuerySeparator('&amp;');
echo $formatter->getQuerySeparator(); //return &amp;
$query = Query::createFromArray(['foo' => 'ba r', "baz" => "bar"]);
echo $query; //displays foo=ba%20&baz=bar
echo $formatter->format($query); //displays foo=ba%20r&amp;baz=bar
echo $formatter($query);         //displays foo=ba%20r&amp;baz=bar
~~~

### Preserving URI components

~~~php
<?php

public Formatter::preserveQuery(bool $status): void
public Formatter::preserveFragment(bool $status): void
~~~~~~

<p class="message-notice">New in <code>version 4.1</code></p>

According to PSR-7 UriInterface, when a component is empty and optional, this is the case of the query and the fragment components, it is completely removed from the object representation. The following URIs will all produced the same string representation:

~~~php
<?php

use League\Uri\Schemes\Http;

$uri = Http::createFromString('http://uri.thephpleague.com?#');
$altUri = Http::createFromString('http://uri.thephpleague.com#');
$otherUri = Http::createFromString('http://uri.thephpleague.com?');

echo $uri->__toString();      //return 'http://uri.thephpleague.com';
echo $altUri->__toString();   //return 'http://uri.thephpleague.com';
echo $otherUri->__toString(); //return 'http://uri.thephpleague.com';
~~~

If you need to preserve these URI parts you are required to specify it to the formatter as shown below:

~~~php
<?php

use League\Uri\Formatter;
use League\Uri\Schemes\Http;

$formatter = new Formatter();
$formatter->preserveQuery(true);
$formatter->preserveFragment(true);
echo $formatter($uri);  //return 'http://uri.thephpleague.com?#';

$formatter->preserveQuery(false);
$formatter->preserveFragment(true);
echo $formatter($altUri); //return 'http://uri.thephpleague.com#';

$formatter->preserveQuery(true);
$formatter->preserveFragment(false);
echo $formatter($altUri); //return 'http://uri.thephpleague.com?';
~~~

<p class="message-notice">By default and to avoid BC break, empty query and fragment URI parts are not preserved.</p>

## Using the Formatter with a complete URI

Apart form URI component classes, the `Formatter::format` method can modify the string representation of any Uri object class.

### Concrete example

~~~php
<?php

use League\Uri\Formatter;
use League\Uri\Schemes\Http;

$formatter = new Formatter();
$formatter->setHostEncoding(Formatter::HOST_AS_ASCII);
$formatter->setQueryEncoding(PHP_QUERY_RFC3986);
$formatter->setQuerySeparator('&amp;');
$formatter->preserveFragment(true);

echo $formatter->format(Http::createFromString('https://рф.ru:81?foo=ba%20r&baz=bar'));
echo $formatter(Http::createFromString('https://рф.ru:81?foo=ba%20r&baz=bar'));
//displays https://xn--p1ai.ru:81?foo=ba%20r&amp;baz=bar#
~~~