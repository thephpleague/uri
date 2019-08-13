---
layout: default
title: Host modifiers
---

Host modifiers
=======

The following modifiers update and normalize the URI host component according to RFC3986 or RFC3987.

## UriModifier::hostToAscii

Converts the host into its ascii representation according to RFC3986:

~~~php
<?php

use GuzzleHttp\Psr7\Uri;
use League\Uri\UriModifier;

$uri = new Uri("http://스타벅스코리아.com/to/the/sky/");
$newUri = UriModifier::hostToAscii($uri);

echo get_class($newUri); //display \GuzzleHttp\Psr7\Uri
echo $newUri; //display "http://xn--oy2b35ckwhba574atvuzkc.com/to/the/sky/"
~~~

<p class="message-warning">This method will have no effect on <strong>League URI objects</strong> as this conversion is done by default.</p>

## UriModifier::hostToUnicode

Converts the host into its idn representation according to RFC3986:

~~~php
<?php

use GuzzleHttp\Psr7\Uri;
use League\Uri\Modifiers\HostToUnicode;

$uriString = "http://xn--oy2b35ckwhba574atvuzkc.com/to/the/./sky/";
$uri = new Uri($uriString);
$newUri = UriModifier::hostToUnicode($uri);

echo get_class($newUri); //display \GuzzleHttp\Psr7\Uri
echo $newUri; //display "http://스타벅스코리아.com/to/the/sky/"
~~~

<p class="message-warning">This method will have no effect on <strong>League URI objects</strong> because the object always transcode the host component into its RFC3986/ascii representation.</p>

## UriModifier::removeZoneIdentifier

Removes the host zone identifier if present

~~~php
<?php

use Zend\Diactoros\Uri;
use League\Uri\Modifiers\RemoveZoneIdentifier;

$uri = new Uri('http://[fe80::1234%25eth0-1]/path/to/the/sky.php');
$newUri = UriModifier::removeZoneIdentifier($uri);
echo get_class($newUri); //display \Zend\Diactoros\Uri

echo $newUri; //display 'http://[fe80::1234]/path/to/the/sky.php'
~~~

## UriModifier::addRootLabel

Adds the root label if not present

~~~php
$uri = Http::createFromString('http://example.com:83');
$newUri = UriModifier::addRootLabel($uri);

echo $newUri; //display 'http://example.com.:83'
~~~

## UriModifier::removeRootLabel

Removes the root label if present

~~~php
$uri = Http::createFromString('http://example.com.#yes');
$newUri = UriModifier::removeRootLabel($uri);

echo $newUri; //display 'http://example.com#yes'
~~~

## UriModifier::appendLabel

Appends a host to the current URI host.

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$newUri = UriModifier::appendLabel($uri, 'fr');

echo $newUri; //display "http://www.example.com.fr/path/to/the/sky/"
~~~

## UriModifier::prependLabel

Prepends a host to the current URI path.

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$newUri = UriModifier::prependLabel($uri, 'shop');

echo $newUri; //display "http://shop.www.example.com/path/to/the/sky/and/above"
~~~

## UriModifier::replaceLabel

Replaces a label from the current URI host with a host.

<p class="message-notice">Hosts are hierarchical components whose labels are indexed from right to left.</p>

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$newUri = UriModifier::replaceLabel($uri, 2, 'admin.shop');

echo $newUri; //display"http://admin.shop.example.com/path/to/the/sky"
~~~

<p class="message-info">This modifier supports negative offset</p>

The previous example can be rewritten using negative offset:

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$newUri = UriModifier::replaceLabel($uri, -1, 'admin.shop');

echo $newUri; //display"http://admin.shop.example.com/path/to/the/sky"
~~~

## UriModifier::removeLabels

Removes selected labels from the current URI host. Labels are indicated using an array containing the labels offsets.

<p class="message-notice">Hosts are hierarchical components whose labels are indexed from right to left.</p>

~~~php
$uri = Http::createFromString("http://www.localhost.com/path/to/the/sky/");
$newUri = UriModifier::removeLabels($uri, 2, 0);

echo $newUri; //display "http://localhost/path/the/sky/"
~~~

<p class="message-info">This modifier supports negative offset</p>

The previous example can be rewritten using negative offset:

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$newUri = UriModifier::removeLabels($uri, -1, -3);

echo $newUri; //display "http://localhost/path/the/sky/"
~~~
