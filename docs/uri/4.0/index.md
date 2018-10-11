---
layout: default
title: Uri
redirect_from:
    - /4.0/
---

# Overview

[![Author](//img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square)](https://twitter.com/nyamsprod)
[![Source Code](//img.shields.io/badge/source-league/uri-blue.svg?style=flat-square)](https://github.com/thephpleague/uri)
[![Latest Stable Version](//img.shields.io/github/release/thephpleague/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)
[![Software License](//img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)<br>
[![Build Status](//img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri)
[![Code Coverage](//img.shields.io/scrutinizer/coverage/g/thephpleague/csv.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/uri/?branch=master)
[![Quality Score](//img.shields.io/scrutinizer/g/thephpleague/uri.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/uri)
[![Total Downloads](//img.shields.io/packagist/dt/league/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)

The library provides simple and intuitive classes to [instantiate](/uri/instantiation/) and [manipulate](/uri/4.0/uri/manipulation/) URIs and their [components](/uri/4.0/components/overview/) in PHP. Out of the box the library handles the following schemes:

- [HTTP/HTTPS](/uri/4.0/uri/schemes/http/);
- [Websockets](/uri/4.0/uri/schemes/ws/);
- [FTP](/uri/4.0/uri/schemes/ftp/);
- [Data URIs](/uri/4.0/uri/schemes/data-uri/);

and allow [to easily manage others scheme specific URIs](/4.0/uri/extension/).

The library ships with:

- a [RFC3986](http://tools.ietf.org/html/rfc3986) compliant parser for the [URI string](/4.0/services/parser-uri/);
- a parser for the [URI query string](/uri/4.0/services/parser-query/) that preserves its content;
- a [URI formatter](/uri/4.0/services/formatter/) to easily output URI strings;

## Usages

### Getting information from a URI

Apart from being able to get all the URI component strings using their respective getter methods, the URI object also exposes all component as objects throught PHP's magic `__get` method. You can use this ability to get even more information about the URI objects.

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString("http://uri.thephpleague.com/.././report/");
echo $uri->getPath(), PHP_EOL; //display "/.././report/"
$normalizedPath = $uri->path
    ->withoutLeadingSlash()
    ->withoutTrailingSlash()
    ->withoutDotSegments();
echo $normalizedPath, PHP_EOL; //display "report"

var_dump($uri->host->toArray());
// display
// array(
//     0 => 'com',
//     1 => 'thephpleague',
//     2 => 'uri',
//);

echo $uri->getHost(), PHP_EOL; //display "uri.thephpleague.com"
echo $uri->host->getLabel(2), PHP_EOL; //display "uri"
echo $uri->host->getPublicSuffix(), PHP_EOL; //return com
echo $uri->host->getRegisterableDomain(), PHP_EOL; //display 'thephpleague.com'
echo $uri->host->getSubDomain(), PHP_EOL; //display 'uri'
~~~

Each component exposes its own specific properties. Please refer to the documentation to get the full public API.

### Using URI Modifiers

The package comes bundle with [URI modifiers](/uri/4.0/uri/manipulation/#uri-modifiers) which enable modifying any League URI object as well as any PSR-7 `UriInterface` objects in a simple and intuitive way.

Let's say you have a document that can be downloaded in different format (CSV, XML, JSON) and you quickly want to generate each format URI. This example illustrates how easy it is to generate theses different URIs from an original URI.

~~~php
<?php

use League\Uri\Modifiers\AppendSegment;
use League\Uri\Modifiers\Extension;
use League\Uri\Modifiers\Pipeline;
use League\Uri\Modifiers\ReplaceLabel;
use League\Uri\Schemes\Http as HttpUri;

//let's create the original URI
//You cand switch this League object with any PSR-7 UriInterface compatible object
$uri = HttpUri::createFromString("http://www.example.com/report");

//using the Pipeline URI modifier class
//we register and apply the common transformations
$modifiers = (new Pipeline())
    ->pipe(new AppendSegment('/purchases/summary'))
    ->pipe(new ReplaceLabel(3, 'download'));
$tmpUri = $modifiers->process($uri->withScheme('https'));

//the specific transformation are applied here
$links = [];
foreach (['csv', 'json', 'xml'] as $extension) {
    $links[$extension] = (new Extension($extension))->__invoke($tmpUri);
}

// $links is an array of League\Uri\Schemes\Http objects
echo $uri, PHP_EOL;           // display "http://www.example.com/report"
echo $links['csv'], PHP_EOL;  // display "https://download.example.com/report/purchases/summary.csv"
echo $links['xml'], PHP_EOL;  // display "https://download.example.com/report/purchases/summary.xml"
echo $links['json'], PHP_EOL; // display "https://download.example.com/report/purchases/summary.json"
~~~
