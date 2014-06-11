# League.url


[![Build Status](https://travis-ci.org/thephpleague/url.png?branch=master)](https://travis-ci.org/thephpleague/url)
[![Code Coverage](https://scrutinizer-ci.com/g/thephpleague/url/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/thephpleague/url/?branch=master)
[![Total Downloads](https://poser.pugx.org/league/url/downloads.png)](https://packagist.org/packages/league/url)
[![Latest Stable Version](https://poser.pugx.org/league/url/v/stable.png)](https://packagist.org/packages/league/url)

The League Url package provides simple and intuitive classes and methods to create and manage Urls in PHP. 

This package is compliant with [PSR-2][], and [PSR-4][].

[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md

## Install

Via Composer:

```json
{
    "require": {
        "League\url": "3.*"
    }
}
```

## System Requirements

You need **PHP >= 5.3.0** to use `League\Url` but the latest stable version of PHP is recommended.

## Instantiation

The easiest way to get started is to add `'/path/to/League/url/src'` to your PSR-4 compliant Autoloader. Once added to the autoloader you can easily instantiate your url:

```php
<?php

require 'vendor/autoload.php' //when using composer

use League\Url\Factory;

$url_factory = new Factory(PHP_QUERY_RFC1738);

//Method 1 : from a given string
$url = new $url_factory->createFromString('http://www.example.com');
$url_immutable = new $url_factory->createFromString('http://www.example.com', Factory::URL_IMMUTABLE);

//Method 2: from the current PHP page
//don't forget to provide the $_SERVER array
$url = $url_factory->createFromServer($_SERVER); 
$url_immutable = $url_factory->createFromServer($_SERVER, Factory::URL_IMMUTABLE);
```
**Of note:**

The constructor optional argument `$enc_type` specifies how to encode the URL query component using the following PHP internal constant:

* `PHP_QUERY_RFC1738`: encode the URL query component following the [RFC 3968](http://www.faqs.org/rfcs/rfc1738)
* `PHP_QUERY_RFC3968`: encode the URL query component following the [RFC 1738](http://www.faqs.org/rfcs/rfc3968)

By default if no `$enc_type` argument is given, the URL query component is encoded using RFC 1738.

The second optional argument for `createFromServer` and `createFromString` methods `$mutable_state` specifies the object to be returned:

* if `$mutable_state` equals `Factory::URL_IMMUTABLE` the factory return a `League\Url\UrlImmutable` object;
* if `$mutable_state` equals `Factory::URL_MUTABLE` the factory return a `League\Url\Url` object;

Both classes implements the `League\Url\UrlInterface` interface but differ in the way they handle their URLs components setter and getter.

By default if no `$is_immutable` is given, a `League\Url\Url` object is returned.

### URL encoding using the Factory

The `League\Url\Factory` implements the `League\Interfaces\EncodingInterface`, this interface provides methods to specify how to encode the query string:

* `setEncoding($enc_type)`: set the encoding constant
* `getEncodingType()`: get the current encoding constant used

```php
$url = new $url_factory->createFromString(
	'http://www.example.com/path/index.php?query=toto+le+heros'
);
$url_factory->setEncoding(PHP_QUERY_RFC3968);
$new_url = new $url_factory->createFromString(
	'http://www.example.com/path/index.php?query=toto+le+heros'
);
echo $url; //remains http://www.example.com?query=toto+le+heros
echo $new_url; //output http://www.example.com?query=toto%20le%20heros
```

## Urls Objects

In addition to the `League\Interfaces\EncodingInterface` interface each Urls object implements the `League\Url\UrlInterface` following methods:

* `__toString` returns the full string representation of the URL;
* `getRelativeUrl` returns the string representation of the URL without the "domain" parts (ie: `scheme`, `user`, `path`, `host`, `port`);
* `getBaseUrl` returns the string representation of the URL without the "Uri" parts (ie: `path`, `query`, `fragment`);
* `sameValueAs` return true if two `League\Url\UrlInterface` object represents the same URL. The comparison is encoding independent.

```php
$url = new $url_factory->createFromString(
	'http://www.example.com/path/index.php?query=toto+le+heros'
);

echo $url->getRelativeUrl(); // /path/index.php?query=toto+le+heros
echo $url->getBaseUrl(); // http://www.example.com
echo $url; // 'http://www.example.com/path/index.php?query=toto+le+heros'

$original_url = $url_factory->createFromString('example.com');
$new_url = $url_factory->createFromString('//example.com', Factory::URL_IMMUTABLE);
$alternate_url = $url_factory->createFromString('//example.com?foo=toto+le+heros');
$url_factory->setEncoding(PHP_QUERY_RFC3968);
$another_url = $url_factory->createFromString('//example.com?foo=toto+le+heros');

$original_url->sameValueAs($new_url); //will return true
$alternate_url->sameValueAs($new_url); //will return false
$alternate_url->sameValueAs($another_url); //will return true
```

In addition to these methods each class implements a setter and a getter method for each URLs components. 

You can use chaining with all the setter methods but the `League\Url\UrlImmutable` never modified itself but return a new object instead.

* `setScheme($data)` set the URL scheme component;
* `setUser($data)` set the URL user component;
* `setPass($data)` set the URL pass component;
* `setHost($data)` set the URL host component;
* `setPort($data)` set the URL port component;
* `setPath($data)` set the URL path component;
* `setQuery($data)` set the URL query component;
* `setFragment($data)` set the URL fragment component;

The `$data` argument can be:

* `null`;
* a valid component string for the specified URL component;
* an object implementing the `__toString` method;
* for `setHost`, `setPath`, `setQuery`: an `array` or a `Traversable` object;

```php
$url = new $url_factory->createFromString('http://www.example.com');
$new_url = $url
		->setUser('john')
		->setPass('doe')
		->setPort(443)
		->setScheme('https');
echo $url; //remains http://www.example.com/
echo $new_url; //output https://john:doe@www.example.com:443/
```

When accessing a Urls component from your URL object the getter method returns a object for each component.

For the `League\Url\UrlImmutable` to avoid modifiying the object by reference it returns a new property object instead.

* `getScheme()` returns a `League\Interfaces\ComponentInterface` object
* `getUser()` returns a `League\Interfaces\ComponentInterface`object
* `getPass()` returns a `League\Interfaces\ComponentInterface`object
* `getHost()` returns a `League\Interfaces\SegmentInterface` object
* `getPort()` returns a `League\Interfaces\ComponentInterface`object
* `getPath()` returns a `League\Interfaces\SegmentInterface` object
* `getQuery()` returns a `League\Interfaces\QueryInterface` object
* `getFragment()` returns a `League\Interfaces\ComponentInterface`object

```php
//From a League\Url\Url object 
$url = new $url_factory->createFromString('https://www.example.com:443');
$port = $url->getPort();
$port->set(80);
echo $port; // output 80;
echo $url->getPort(); // output 80;

//From a League\Url\UrlImmutable object 
$url = new $url_factory->createFromString('https://www.example.com:443', Factory::URL_IMMUTABLE);
$port = $url->getPort(); //$port is a clone object of the URL port component.
$port->set(80);
echo $port; // output 80;
echo $url->getPort(); // remains 443;
```

## URL components classes

Each component class implements the `League\Interfaces\ComponentInterface` with the following public methods:

* `set($data)`: set the component data
* `get()`: returns `null` if the class data is empty or its string representation
* `__toString()`: return a typecast string representation of the component.
* `getUriComponent()`: return an altered string representation to ease URL representation.

The `$data` argument can be:

* `null`;
* a valid component string for the specified URL component;
* an object implementing the `__toString` method;

```php
use League\Url\Components\Scheme;

$scheme = new Scheme;
$scheme->get(); //will return null since no scheme was set
$scheme->set('https');
echo $scheme->__toString(); //will echo 'https'
echo $scheme->getUriComponent(); //will echo 'https://'
```

The URL components that **only** implement this interface are:

* `scheme` with the `League\Url\Components\Scheme`;
* `user` with the `League\Url\Components\User`;
* `pass` with the `League\Url\Components\Pass`;
* `port` with the `League\Url\Components\Port`;
* `fragment` with the `League\Url\Components\Fragment`;

The classes differ on how they validate the data and/or on how they output the component string.

## Complex Components Classes

Classes that deal with Url complex component (ie: `host`, `path`, `query`) implement the following interfaces:

* `Countable`
* `IteratorAggregate`
* `ArrayAccess`
* `League\Interfaces\ComponentArrayInterface`

The `League\Interfaces\ComponentArrayInterface` extends the `League\Interfaces\ComponentInterface` by adding the following methods:

* `toArray()`: will return an array representation of the component;
* `keys()`: will return all the keys or a subset of the keys of an array if a value is given.

**Of note:** The `$data` argument for the `set` method can also be an `array` or a `Traversable` object.

### The `Query` class

This class manage the URL query component and implements the following interfaces:

* the `League\Interfaces\EncodingInterface`;
* the `League\Interfaces\QueryInterface` which extends the `League\Interfaces\ComponentArrayInterface` by adding the following method:

	* `modify($data)`: update the component data;

By default, the `Query` class encode its members using the RFC #1738

Example using the `League\Url\Components\Query` object:

```php
use League\Url\Components\Query;

$query = new Query('foo=bar', PHP_QUERY_RFC1738);
$query['baz'] = 'troll';
$query['toto'] = 'le heros';
foreach ($query as $offset => $value) {
	echo "$offset => $value".PHP_EOL;
}
//will echo 
// foo => bar
// baz => troll
// toto => le heros

$query->modify(array('foo' => 'baz', 'toto' => null));
//by setting toto to null
//you remove the toto argument from the query_string

$found = $query->keys('troll');
//$found equals array(0 => 'baz')

echo count($query); //will return 2;
echo $query; //will display foo=baz&baz=troll;
$query->setEncoding(PHP_QUERY_RFC3968); //for PHP 5.3
$query->modify(array('toto' => 'le gentil'));
echo $query; //will display foo=baz&baz=troll&toto=le%20gentil;
```

### The `Path` and `Host` classes

These classes manage the URL path and host components. They only differs in the way they validate their data. Both classes implements the `League\Interfaces\SegmentInterface` which extends the `League\Interfaces\ComponentArrayInterface` by adding the following methods:

* `append($data, $whence = null, $whence_index = null)`: append data into the component;
* `prepend($data, $whence = null, $whence_index = null)`: prepend data into the component;
* `remove($data)`: remove data from the component;

The arguments:

* The `$data` argument can be `null`, a valid component string, a object implementing the `__toString` method, an array or a `Traversable` object;
* The `$whence` argument specify the string segment where to include the data;
* The `$whence_index` argument specify the `$whence` index if it is present more than once in the object;
* When using the `remove` method, if the pattern is present multiple times only the first match found is removed* 

*Tips: You can easily get the `$whence_index` by using the `ComponentArrayInterface::keys($whence)` method result.*

Example using the `League\Url\Components\Path` object:

```php
use League\Url\Components\Path;

$path = new Path;
$path[] = 'bar';
$path[] = 'troll';
foreach ($path as $offset => $value) {
	echo "$offset => $value".PHP_EOL;
}
//will echo 
// 0 => bar
// 1 => troll

$path->append('leheros/troll', 'bar');

$found = $path->keys('troll');
//$found equals array(0 => '2');

echo count($path); //will return 4;
echo $path; //will display bar/leheros/troll/troll
var_export($path->toArray())
//will display
// array(
//    0 => 'bar',
//    1 => 'toto',
//    2 => 'troll',
//    3 => 'troll'
// )

$path->prepend('bar', 'troll', 1);
echo $path->get(); //will display bar/leheros/troll/bar/troll
$path->remove('troll/bar');
echo (string) $path; //will display bar/leheros/troll
```

Testing
-------

``` bash
$ phpunit
```

Contributing
-------

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

Credits
-------

- [ignace nyamagana butera](https://github.com/nyamsprod)
- [All Contributors](graphs/contributors)

License
-------

The MIT License (MIT). Please see [License File](LICENSE) for more information.
