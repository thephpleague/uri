Bakame.url
======

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

You need **PHP >= 5.4.0** to use Bakame Url but the latest stable version of PHP is recommended.

Instantiation
-------------

The easiest way to get started is to added `'/path/to/Bakame/Entity/src'` to your PSR-0 compliant Autoloader


Basic Usage
-----------

### 1. There are 3 way to instantiate the a new Bakame/Url/Url object provider by the Bakame/Url/Factory Class.


```php
<?php


use Bakame\Url\Factory;

//Method 1 : from a given string
$url = Factory::createFromString('http://www.example.com'); // you've created a new Url object from this string 

//Method 2: from the current PHP page
$url = Factory::createFromServer($_SERVER); //don't forget to provide the $_SERVER array

//Method 3: a "naked" URL
$url = Factory::create();
```

### 2. now you can manipulate $url as you wish

```php
<?php

$url
    ->setQuery('computer', 'os') //adding on query data
    ->setQuery(['foo' => 'bar', 'bar' => 'baz']) //add more query data using an array
    ->setPath('windows') //add a directory path named 'windows' at the end of the URL path
    ->setPath('linux', 'prepend', 'windows') //adding linux directory path before 'window'
    ->setPath('iOS', 'append', 'windows')  //adding iOS directory path after 'window'
    ->setAuth(['user' => 'john', 'pass' => 'doe']) //adding auth information
    ->setScheme('https')
    ->setPort(443)
    ->unsetHost() //remove any host information if present
    ->setHost(['api', 'ejamplo', 'com']);

echo $url; // will output https://john:doe@api.ejamplo.com:443/linux/windows/iOS?computer=os&foo=bar&bar=baz
```

Please don't hesitate to refer to the source to get more advanced feature.