---
layout: homepage
---

# Features

## Parsing URI

The League URI Parser parses any given URI according to RFC3986 rules

~~~php
<?php

use League\Uri;

var_export(Uri\parse('http://foo.com?@bar.com/'));
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

use League\Uri;

$uri = Uri\create("hTTp://www.ExAmPLE.com:80/hello/./wor ld?who=f 3#title");
echo $uri; //displays http://www.example.com/hello/./wor%20ld?who=f%203#title

$uri = Uri\Http::createFromComponent(parse_url("hTTp://www.bébé.be?#"));
echo $uri; //displays http://xn--bb-bjab.be?#
~~~

## URI Middlewares

A collection of URI middlewares to enable reliable URI modifications with any given League URI object or [PSR-7](http://www.php-fig.org/psr/psr-7/) `UriInterface` compatible implementation.

~~~php
<?php

use League\Uri;
use Zend\Diactoros\Uri as DiactorosUri;

$uri = new DiactorosUri("http://www.example.com?fo.o=toto#~typo");
$new_uri = Uri\merge_query($uri, 'fo.o=bar&taz=');
echo $new_uri;
// display http://www.example.com?fo.o=bar&taz=#~typo
// $new_uri is a Zend\Diactoros\Uri instance
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
