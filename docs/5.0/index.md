---
layout: default
title: Uri
---

# Overview

[![Author](//img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square)](https://twitter.com/nyamsprod)
[![Source Code](//img.shields.io/badge/source-league/uri-blue.svg?style=flat-square)](https://github.com/thephpleague/uri)
[![Latest Stable Version](//img.shields.io/github/release/thephpleague/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)
[![Software License](//img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](//img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri)
[![Total Downloads](//img.shields.io/packagist/dt/league/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)

The library is a **meta package** which provides simple and intuitive classes to [parse](/5.0/parser/), [validate](/5.0/uri/) and [manipulate](/5.0/manipulations/) URIs and their [components](/5.0/components/) in PHP. Out of the box the library validate the following URI specific schemes:

- HTTP/HTTPS;
- Websockets;
- FTP;
- Data URIs;
- File URIs;

and allow to easily manage others scheme specific URIs.

The library ships with:

- a [RFC3986][] and [RFC3987][] compliant parser for the [URI string](/5.0/parser/);
- a [URI formatter](/5.0/manipulations/formatter/) to easily output [RFC3987][] URI strings;
- [URI middlewares](/5.0/manipulations/middlewares/) and functions to ease URI manipulations;

## Usage examples

### Getting information from a URI

Appart from being able to get all the URI component string using their respective getter method.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Components\HierarchicalPath;
use League\Uri\Components\Host;

$uri = Http::createFromString("http://uri.thephpleague.com/.././report/");
echo $uri->getPath(), PHP_EOL; //display "/.././report/"
$normalizedPath = (new HierarchicalPath($uri->getPath()))
    ->withoutLeadingSlash()
    ->withoutTrailingSlash()
    ->withoutDotSegments();
echo $normalizedPath, PHP_EOL; //display "report"

$host = new Host($uri->getHost());
var_dump($host->getLabels());
// display
// array(
//     0 => 'com',
//     1 => 'thephpleague',
//     2 => 'uri',
//);

echo $host, PHP_EOL; //display "uri.thephpleague.com"
echo $host->getLabel(2), PHP_EOL; //display "uri"
echo $host->getPublicSuffix(), PHP_EOL; //return com
echo $host->getRegisterableDomain(), PHP_EOL; //display 'thephpleague.com'
echo $host->getSubDomain(), PHP_EOL; //display 'uri'
~~~

Each component exposes its own specific properties. Please refer to the documentation to get the full public API.

### Using URI Middlewares

The package comes bundle with [URI middlewares](/uri/manipulations/) which enable modifying any URI object in a simple and intuitive way.

Let's say you have a document that can be downloaded in different format (CSV, XML, JSON) and you quickly want to generate each format URI. This example illustrates how easy it is to generate theses different URIs from an original URI.

~~~php
<?php

use League\Uri\Modifiers\AppendSegment;
use League\Uri\Modifiers\Extension;
use League\Uri\Modifiers\Pipeline;
use League\Uri\Modifiers\ReplaceLabel;
use GuzzleHttp\Psr7\Uri as GuzzleUri;

//let's create the original URI
//The URI Middlewares works with any PSR-7 implementation
$uri = new GuzzleUri("http://www.example.com/report");

//using the Pipeline URI modifier class
//we register and apply the common transformations
$modifiers = (new Pipeline())
    ->pipe(new AppendSegment('/purchases/summary'))
    ->pipe(new ReplaceLabel(-1, 'download'));
$tmpUri = $modifiers->process($uri->withScheme('https'));

//the specific transformation are applied here
$links = [];
foreach (['csv', 'json', 'xml'] as $extension) {
    $links[$extension] = (new Extension($extension))->process($tmpUri);
}

// $links is an array of League\Uri\Schemes\Http objects
echo $uri, PHP_EOL;           // display "http://www.example.com/report"
echo $links['csv'], PHP_EOL;  // display "https://download.example.com/report/purchases/summary.csv"
echo $links['xml'], PHP_EOL;  // display "https://download.example.com/report/purchases/summary.xml"
echo $links['json'], PHP_EOL; // display "https://download.example.com/report/purchases/summary.json"
echo get_class($links['json']), PHP_EOL; // display "GuzzleHttp\Psr7\Uri"
~~~



[RFC3986]: http://tools.ietf.org/html/rfc3986
[RFC3987]: http://tools.ietf.org/html/rfc3987
[@nyamsprod]: https://twitter.com/nyamsprod