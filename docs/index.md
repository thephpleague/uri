---
layout: homepage
---

# Overview

## Parsing URI

The League URI Parser parses any given URI according to RFC3986 rules

~~~php
<?php

use League\Uri\Parser;

$parser = new Parser();
var_export($parser('http://foo.com?@bar.com/'));
//returns the following array
//array(
//  'scheme' => 'http',
//  'user' => null,
//  'pass' => null,
//  'host' => 'foo.com',
//  'port' => null,
//  'path' => '',
//  'query' => '@bar.com/',
//  'fragment' => null,
//);
~~~

## Normalizing URI

League URI objects normalize the URI string according to RFC3986/RFC3987 non destructive rules.

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString("hTTp://www.ExAmPLE.com:80/hello/./wor ld?who=f 3#title");
echo $uri; //displays http://www.example.com/hello/./wor%20ld?who=f%203#title

$uri = HttpUri::createFromComponent(parse_url("hTTp://www.bébé.be?#"));
echo $uri; //displays http://xn--bb-bjab.be?#
~~~

## URI Middlewares

A collection of URI middlewares to enable reliable URI modifications.

~~~php
<?php

use League\Uri\Modifiers\MergeQuery;
use League\Uri\Schemes\Http as HttpUri;

$base_uri = "http://www.example.com?fo.o=toto#~typo";
$query_to_merge = 'fo.o=bar&taz=';

$uri = HttpUri::createFromString($base_uri);
$modifier = new MergeQuery($query_to_merge);

$new_uri = $modifier->process($uri);
echo $new_uri;
// display http://www.example.com?fo.o=bar&taz=#~typo
~~~

## URI components

Improve URI components manipulations through dedicated objects.

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('www.example.co.uk');
echo $host->getPublicSuffix();        //display 'co.uk'
echo $host->getRegisterableDomain();  //display 'example.co.uk'
echo $host->getSubdomain();           //display 'www'
$host->isPublicSuffixValid();         //return a boolean 'true' in this example
~~~
