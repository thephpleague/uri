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

use League\Url\Factory as Url;

//Method 1 : from a given string
$url = new Url::createFromString('http://www.example.com'); // you've created a new Url object from this string 

//Method 2: from the current PHP page
$url = Url::createFromServer($_SERVER); //don't forget to provide the $_SERVER array
```

`$url` is a valid `League\Url\Url` object. This is the main value object we will be using to manipulate the url.

Usage
-------

`League\Url` is a Immutable Value Object everytime you modify the object property you create a new object. 

You can easily manipulating the Url with chaining like below :

```php

$url = new Url::createFromString('http://www.example.com');

$url2 = $url->setUser('john')->setPass('doe')->setPort(443)->setScheme('https');
echo $url2; //output https://john:doe@www.example.com:443/
echo $url; //remains http://www.example.com/

$url3 = $url2->modify(['query' => 'value']);
echo $url3 //output https://john:doe@www.example.com:443/?query=value
echo $url2; //remains https://john:doe@www.example.com:443/
```

For each component there is a specific setter and getter:

* `setScheme($data)` : set the URL Scheme component **only accept (http, https)**
* `getScheme()` : returns a `League\Url\Components\Scheme` object
* `setUser($data)` : set the URL User component
* `getUser()` : returns a `League\Url\Components\Component` object
* `setPass($data)` : set the URL Password component
* `getPass()` : returns a `League\Url\Components\Scheme` object
* `setHost($data)` : set the URL Host component
* `getHost()` : returns a `League\Url\Components\Component` object
* `setPath($data)` : set the URL Path component
* `getPath()` : returns a `League\Url\Components\Scheme` object
* `setQuery($data)` : set the URL Query componentobject
* `getQuery()` : returns a `League\Url\Components\Query` object
* `setFragment($data)` : set the URL Fragment component
* `getFragment()` : returns a `League\Url\Components\Component` object

Of note: 

* The `$data` argument can be null or a valid component string. For complex components like `Host`, `Path` and `Query` `$data` can also be an array or a `Traversable` object;
* All the getter return a `League\Interfaces\ComponentInterface` object. this means they all provide:
	* a `get` method;
	* a `set` method;
	* implements the `__toString` method;
* To keep the object Immutable the return object from the getters method are all clones, **so manipulating them separately won't affect the original object.**

**Tips:** Nothing prevents you from setting back your manipulated object to the main URL using the setters methods

For the more complex component, in addition to the setter you can manipulate the exisiting component
more easily with the following methods:

* `appendHost($data, $whence = null, $whence_index = null)` : append Host info to the component
* `prependHost($data, $whence = null, $whence_index = null)` : prepend Host info to the component
* `removeHost($data)` : remove Host info from the component

* `appendPath($data, $whence = null, $whence_index = null)` : append Path info to the component
* `prependPath($data, $whence = null, $whence_index = null)` : prepend Path info to the component
* `removePath($data)` : remove Path info from the component

* `modifyQuery($data)` : update the URL Query component 

Of note:

* The `$data` argument can be null, a valid component string, an array or a `Traversable` object;
* The `$whence` argument specify where to include the appended data;
* The `$whence_index` argument specify the `$whence` index if it is present more than once in the object;
 
*When removing `Host` or `Path`, when the pattern is present multiple times only the first match found is removed*  

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