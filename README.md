Bakame.url
======

[![Build Status](https://travis-ci.org/nyamsprod/Bakame.url.png)](https://travis-ci.org/nyamsprod/Bakame.url)


The Bakame Url package provides simple and intuitive classes and methods to create and manage Urls in PHP. 

This package is compliant with [PSR-0][], [PSR-1][], and [PSR-2][].

[PSR-0]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md

Getting Started
===============

Install
-------

You may install the Bakame Url package with Composer (recommended) or manually.

System Requirements
-------------------

You need **PHP >= 5.3.0** to use Bakame Url but the latest stable version of PHP is recommended.

Instantiation
-------------

The easiest way to get started is to added `'/path/to/Bakame/Entity/src'` to your PSR-0 compliant Autoloader. One added to the autoload you can instantiate your url with 3 differents methods as explain below:

```php
<?php


use Bakame\Url\Factory;

//Method 1 : from a given string
$url = Factory::createUrlFromString('http://www.example.com'); // you've created a new Url object from this string 

//Method 2: from the current PHP page
$url = Factory::createUrlFromServer($_SERVER); //don't forget to provide the $_SERVER array

//Method 3: a "naked" URL
$url = Factory::createUrl();
```

These 3 methods will all return a valid `Bakame\Url\Url` object. This is the main object we will be using to manipulate the url.


Basic Usage
------------

Manipulating the Url is simple with chaining, look at the example below:

```php

$url = Factory::createFromString('http://www.example.com');

$url
    ->setUsername('john') //adding username information
    ->setPassword('doe') //adding password information
    ->setScheme('https')
    ->setPort(443);
```
For the more complex component `Bakame\Url\Url` returns a more specific class dedicated to manipulate them.

```php
$query = $url->query(); //return a Bakame\Url\Components\Query
$query
    ->set('computer', 'os') //adding on query data
    ->set(['foo' => 'bar', 'bar' => 'baz']); //add more query data using an array

$path = $url->path(); //return a Bakame\Url\Components\Path
$path
    ->set('windows') //add a directory path named 'windows' at the end of the URL path
    ->set('linux', 'prepend', 'windows') //adding linux directory path before 'window'
    ->set('iOS', 'append', 'windows');  //adding iOS directory path after 'window'

$host = $url->host(); //return a Bakame\Url\Components\Host
$host
    ->clear() //remove any host information if present
    ->set(['api', 'ejamplo', 'com']);

echo $url; // will output https://john:doe@api.ejamplo.com:443/linux/windows/iOS?computer=os&foo=bar&bar=baz

$urlbis = clone $url; //When cloning the references class will also be clone to dereference the 2 classes.

```

Urls Components Classes
---------------

The Bakame.url package comes bundle with Utility classes that can help you manipulate a single portion of you url. Let's say that you are only interested in modifying the query string from a given URL. You can proceed like so:

```php 
use Bakame\Url\Components\Query;

$string = $_SERVER['QUERY_STRING'];

$query = new Query($string);

$query
    ->set('toto', 'leheros')
    ->set(['foo' => 'bar', 'bar' => 'baz']);

$query->get('toto') //will return 'leheros';
$query->all(); //will return the data in form of an array
$query->clear(); //will empty the query string
$string = $query->__toString(); // $string is now equals to "?toto=leheros&foo=bar&bar=baz"

```
The `Bakame\Url\Components\Query` implements the `Countable`, `IteratorAggrete` and `ArrayAccess` interfaces, so the following is possible:

```php 
$query['mynameis'] = 'slimshady';
foreach ($query as $key => $value) {
    //do something here....
}

echo count($query); //return the number of property set

```

There are seven (7) component classes for each URL part:

* `Bakame\Url\Components\Scheme` Manipulate the `scheme` component
* `Bakame\Url\Components\Auth` Manipulate the `user` and `pass` components **together**
* `Bakame\Url\Components\Components\Host` Manipulate the `host`
* `Bakame\Url\Components\Port`  Manipulate the `port` component
* `Bakame\Url\Components\Path` Manipulate the `path` component
* `Bakame\Url\Components\Query`  Manipulate the `query` component
* `Bakame\Url\Components\Fragment`  Manipulate the `fragment` component

Please refer to each class documentation to see what they can or can not do.