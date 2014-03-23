League.url
======

[![Build Status](https://travis-ci.org/nyamsprod/League.url.png)](https://travis-ci.org/nyamsprod/League.url)
[![Total Downloads](https://poser.pugx.org/League/Url/downloads.png)](https://packagist.org/packages/League/Url)
[![Latest Stable Version](https://poser.pugx.org/League/Url/v/stable.png)](https://packagist.org/packages/League/Url)

The League Url package provides simple and intuitive classes and methods to create and manage Urls in PHP. 

This package is compliant with [PSR-1][], [PSR-2][], and [PSR-4][].

[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md

Install
-------

You may install the League Url package with Composer (recommended) or manually.

```json
{
    "require": {
        "League\url": "3.*"
    }
}
```


System Requirements
-------

You need **PHP >= 5.3.0** to use League Url but the latest stable version of PHP is recommended.

Instantiation
-------

The easiest way to get started is to add `'/path/to/League/url/src'` to your PSR-4 compliant Autoloader. Once added to the autoloader you can instantiate your url with 3 differents methods as explain below:

```php
<?php


use League\Url\Factory;

//Method 1 : from a given string
$url = Factory::createUrlFromString('http://www.example.com'); // you've created a new Url object from this string 

//Method 2: from the current PHP page
$url = Factory::createUrlFromServer($_SERVER); //don't forget to provide the $_SERVER array

//Method 3: a "naked" URL
$url = Factory::createUrl();
```

These 3 methods will all return a valid `League\Url\Url` object. This is the main object we will be using to manipulate the url.


Usage
-------

Manipulating the Url is simple with chaining, look at the example below:

```php

$url = Factory::createFromString('http://www.example.com');

$url
    ->setUsername('john') //adding username information
    ->setPassword('doe') //adding password information
    ->setScheme('https')
    ->setPort(443);
```
For the more complex component `League\Url\Url` returns a more specific class dedicated to manipulate them.

```php
$query = $url->query(); //return a League\Url\Components\Query
$query
    ->set('computer', 'os') //adding on query data
    ->set(['foo' => 'bar', 'bar' => 'baz']); //add more query data using an array

$path = $url->path(); //return a League\Url\Components\Path
$path
    ->set('windows') //add a directory path named 'windows' at the end of the URL path
    ->set('linux', 'prepend', 'windows') //adding linux directory path before 'window'
    ->set('iOS', 'append', 'windows');  //adding iOS directory path after 'window'

$host = $url->host(); //return a League\Url\Components\Host
$host
    ->clear() //remove any host information if present
    ->set(['api', 'ejamplo', 'com']);

echo $url; // will output https://john:doe@api.ejamplo.com:443/linux/windows/iOS?computer=os&foo=bar&bar=baz

$urlbis = clone $url; //When cloning the references class will also be clone to dereference the 2 classes.

```

Urls Components Classes
-------

The `League\Url` library relies on components classes that represents each part of a URL. 
Let's say that you are only interested in modifying the query string from a given URL. 
You can proceed like so:

```php 
use League\Url\Components\Query;

$string = $_SERVER['QUERY_STRING'];

$query = new Query($string);

$query->clear(); //will empty the query string
$query
    ->set('toto', 'leheros') //setting a single value
    ->set(['foo' => 'bar', 'bar' => 'baz']); //setting multiple values using an array or another Query instance

$query->get('toto') //will return 'leheros';
$query->all(); //will return the data in form of an array
$string = $query->__toString(); // $string is now equals to "toto=leheros&foo=bar&bar=baz"

```
The `League\Url\Components\Query` implements the `Countable`, `IteratorAggrete` and `ArrayAccess` interfaces.

There are seven (7) component classes for each URL part:

* `League\Url\Components\Scheme` Manipulate the `scheme` component
* `League\Url\Components\Auth` Manipulate the `user` and `pass` components **together**
* `League\Url\Components\Host` Manipulate the `host`
* `League\Url\Components\Port`  Manipulate the `port` component
* `League\Url\Components\Path` Manipulate the `path` component
* `League\Url\Components\Query`  Manipulate the `query` component
* `League\Url\Components\Fragment`  Manipulate the `fragment` component

Please refer to each class documentation to see what they can or can not do.

Testing
-------

``` bash
$ phpunit
```

Contributing
-------

Please see [CONTRIBUTING](https://github.com/nyamsprod/League.url/blob/master/CONTRIBUTING.md) for details.

Credits
-------

- [ignace nyamagana butera](https://github.com/nyamsprod)