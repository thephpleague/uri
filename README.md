League.url
======

[![Build Status](https://travis-ci.org/thephpleague/url.png?branch=master)](https://travis-ci.org/thephpleague/url)
[![Coverage Status](https://coveralls.io/repos/thephpleague/url/badge.png)](https://coveralls.io/r/thephpleague/url)

The League Url package provides simple and intuitive classes and methods to create and manage Urls in PHP. 

This package is compliant with [PSR-2][], and [PSR-4][].

[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
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

The easiest way to get started is to add `'/path/to/League/url/src'` to your PSR-4 compliant Autoloader. Once added to the autoloader you can easily instantiate your url:

```php
<?php

use League\Url\Url;

//Method 1 : from a given string
$url = new Url('http://www.example.com'); // you've created a new Url object from this string 

//Method 2: from the current PHP page
$url = Url::createFromServer($_SERVER); //don't forget to provide the $_SERVER array
```

`$url` is a valid `League\Url\Url` object. This is the main value object we will be using to manipulate the url.

Usage
-------

League\Url is a Immutable Value Object everytime you modify the object property you create a new object. 

You can easily manipulating the Url with chaining like below :

```php

$url = new Url('http://www.example.com');

$url2 = $url->setUser('john')->setPass('doe')->setPort(443)->setScheme('https');
echo $url2; //output https://john:doe@www.example.com:443/
echo $url; //remains http://www.example.com/

$url3 = $url2->modify(['query' => 'value']);
echo $url3 //output https://john:doe@www.example.com:443/?query=value
echo $url2; //remains https://john:doe@www.example.com:443/
```

For each component there is a specific setter:

* `setScheme($scheme)` : set the URL Scheme component can be null but **only accept (http, https or `null`)**
* `setUser($user)` : set the URL User component can be null
* `setPass($pass)` : set the URL Password component can be null
* `setFragment($fragment)` : set the URL Fragment component can be null

For the more complex component, in addition to the usual setter you can manipulate the exisiting component
more easily

* `setQuery($data)` : set the URL Query componentobject
* `modifyQuery($data)` : update the URL Query component 

* `setHost($data)` : set the URL Host component
* `appendHost($data, $whence = null, $whence_index = null)` : append Host info to the component
* `prependHost($data, $whence = null, $whence_index = null)` : prepend Host info to the component
* `removeHost($data)` : remove Host info from the component

* `setPath($data)` : set the URL Path component
* `appendPath($data, $whence = null, $whence_index = null)` : append Path info to the component
* `prependPath($data, $whence = null, $whence_index = null)` : prepend Path info to the component
* `removePath($data)` : remove Path info from the component

Of note:

* The `$data` argument can be null, a valid component string, an array or a `Traversable` object;
* The `$whence` argument specify where to include the appended data;
* The `$whence_index` argument specify the `$whence` index if it is present more than once in the object;
 
*When removing Host or Path, when the pattern is present multiple times only the first match found is removed*  

Testing
-------

``` bash
$ phpunit
```

Contributing
-------

Please see [CONTRIBUTING](https://github.com/thephpleague/url/blob/master/CONTRIBUTING.md) for details.

Credits
-------

- [ignace nyamagana butera](https://github.com/nyamsprod)
- [All Contributors](https://github.com/thephpleague/url/graphs/contributors)