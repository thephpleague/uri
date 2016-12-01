---
layout: default
title: Usage
---

# Simple examples

## Getting information from a URI

Appart from being able to get all the URI component string using their respective getter method.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Components\HierarchicalPath;
use League\Uri\Components\Host;

$uri = Http::createFromString("http://uri.thephpleague.com/.././report/");
echo $uri->getPath(), PHP_EOL; //display "/.././report/"
$normalizedPath = (new HierarchicalPath($uri->path))
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

## Using URI Modifiers

The package comes bundle with [URI modifiers](/uri/manipulations/) which enable modifying any League URI object as well as any PSR-7 `UriInterface` objects in a simple and intuitive way.

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
