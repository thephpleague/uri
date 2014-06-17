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

## Requirements

You need **PHP >= 5.3.0** to use the library but the latest stable version of PHP is recommended.

## Usage

The easiest way to get started is to add `'/path/to/League/url/src'` to your PSR-4 compliant Autoloader. Once added to the autoloader you can easily instantiate your url:

```php
<?php

require 'vendor/autoload.php' //when using composer

use League\Url\Url;
use League\url\UrlImmutable;

//Method 1 : from a given string
$url = Url::createFromUrl('http://www.example.com');
$url_immutable = UrlImmutable::createFromUrl('http://www.example.com', PHP_QUERY_RFC3986);

//Method 2: from the current PHP page
//don't forget to provide the $_SERVER array
$url = Url::createFromServer($_SERVER); 
$url_immutable = UrlImmutable::createFromServer($_SERVER);
```

* `$url` is a `League\Url\Url` object
* `$url_immutable` is a `League\Url\UrlImmutable` object

Both objects are value objects implementing the `League\Url\UrlInterface` interface (think of PHP `DateTime` and `DateTimeImmutable` classes which implement the `DateTimeInterface`).

The `createFromServer` and `createFromUrl` methods accept a second optional argument `$enc_type` which specifies which encoding RFC should the URL query string component follow. The following internal PHP constants are the possible values:

* `PHP_QUERY_RFC1738`: encode the URL query component following the [RFC 3968](http://www.faqs.org/rfcs/rfc1738)
* `PHP_QUERY_RFC3986`: encode the URL query component following the [RFC 1738](http://www.faqs.org/rfcs/rfc3968)

By default `$enc_type` equals `PHP_QUERY_RFC1738`.

**Of note: For PHP < 5.4 the constants are defined since they do not exists!**

## Urls Objects

Each Url value object implements the `League\Url\UrlInterface` methods:

* `__toString` returns the full string representation of the URL;
* `getRelativeUrl` returns the string representation of the URL without the "domain" parts (ie: `scheme`, `user`, `path`, `host`, `port`);
* `getBaseUrl` returns the string representation of the URL without the "request uri" part (ie: `path`, `query`, `fragment`);
* `sameValueAs` return true if two `League\Url\UrlInterface` object represents the same URL. The comparison is encoding independent.

```php
$url = Url::createFromUrl('http://www.example.com/path/index.php?query=toto+le+heros');
echo $url->getRelativeUrl(); // /path/index.php?query=toto+le+heros
echo $url->getBaseUrl(); // http://www.example.com
echo $url; // 'http://www.example.com/path/index.php?query=toto+le+heros'

$original_url = Url::createFromUrl('example.com');
$new_url = UrlImmutable::createFromUrl('//example.com');
$alternate_url = Url::createFromUrl('//example.com?foo=toto+le+heros');
$another_url = Url::createFromUrl('//example.com?foo=toto+le+heros', PHP_QUERY_RFC3986);

$original_url->sameValueAs($new_url); //will return true
$alternate_url->sameValueAs($new_url); //will return false
$alternate_url->sameValueAs($another_url); //will return true
```

Additionally, Each class implements a setter and a getter method for each URLs components:

* Chaining is possible since all the setter methods return a `League\Url\UrlInterface` object;
* Getter methods return a component specific object;

**Of note: To stay immutable, the `League\Url\UrlImmutable` object never modified itself but return a new object instead. The object also returns a new property object instead of its own property object to avoid modification by reference.** 

* `setScheme($data)` set the URL scheme component;
* `getScheme()` returns a `League\Url\Components\Scheme` object
* `setUser($data)` set the URL user component;
* `getUser()` returns a `League\Url\Components\User`object
* `setPass($data)` set the URL pass component;
* `getPass()` returns a `League\Url\Components\Pass`object
* `setHost($data)` set the URL host component;
* `getHost()` returns a `League\Url\Components\Host` object
* `setPort($data)` set the URL port component;
* `getPort()` returns a `League\Url\Components\Port`object
* `setPath($data)` set the URL path component;
* `getPath()` returns a `League\Url\Components\Path` object
* `setQuery($data)` set the URL query component;
* `getQuery()` returns a `League\Url\Components\Query` object
* `setFragment($data)` set the URL fragment component;
* `getFragment()` returns a `League\Url\Components\Fragment`object

The `$data` argument can be:

* `null`;
* a valid component string or object for the specified URL component;
* an object implementing the `__toString` method;
* for `setHost`, `setPath`, `setQuery`: an `array` or a `Traversable` object;

```php
//From a League\Url\Url object 
$url = Url::createFromUrl('https://www.example.com');
$url
	->setUser('john')
	->setPass('doe')
	->setPort(443)
	->setScheme('https');
echo $url; // https://john:doe@www.example.com:443/

$port = $url->getPort();
$port->set(80);
echo $port; // output 80;
echo $url->getPort(); // output 80;

//From a League\Url\UrlImmutable object 
$url = UrlImmutable::createFromUrl('http://www.example.com');
$new_url = $url
	->setUser('john')
	->setPass('doe')
	->setPort(443)
	->setScheme('https');
echo $url; //remains http://www.example.com/
echo $new_url; //output https://john:doe@www.example.com:443/

$port = $new_url->getPort(); //$port is a clone object of the URL port component.
$port->set(80);
echo $port; // output 80;
echo $new_url->getPort(); // remains 443;
```

## URL components classes

### Basic components

Each component class implements the `League\Url\Components\ComponentInterface` with the following public methods:

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

### Complex components

Classes that deal with complex components (ie: `host`, `path`, `query`) implement the following interfaces:

* `Countable`
* `IteratorAggregate`
* `ArrayAccess`
* `League\Url\Components\ComponentArrayInterface`

The `League\Url\Components\ComponentArrayInterface` extends the `League\Url\Components\ComponentInterface` by adding the following methods:

* `toArray()`: will return an array representation of the component;
* `keys()`: will return all the keys or a subset of the keys of an array if a value is given.

**Of note:** The `$data` argument for the `set` method can also be an `array` or a `Traversable` object.

### The `Query` class

This class manage the URL query component. 

It implements the following interfaces:

* The `League\Url\Components\EncodingInterface` interface which control how the Query component will be encoded on output:
	* `setEncoding($enc_type)`: set the encoding constant;
	* `getEncoding()`: get the current encoding constant used by the class;

* The `League\Url\Components\QueryInterface` which extends the `League\Url\Components\ComponentArrayInterface` by adding the following method:
	* `modify($data)`: update the component data;

**Of note:** You can also use the class constructor optional argument `$enc_type` to specifies the encoding type. By default `$enc_type` equals `PHP_QUERY_RFC1738`.

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
$query->setEncoding(PHP_QUERY_RFC3986);
$query->modify(array('toto' => 'le gentil'));
echo $query; //will display foo=baz&baz=troll&toto=le%20gentil;
```

### The `Path` and `Host` classes

These classes manage the URL path and host components. They only differs in the way they validate and format before outputting their data. Both classes implements the `League\Url\Components\SegmentInterface` which extends the `League\Url\Components\ComponentArrayInterface` by adding the following methods:

* `append($data, $whence = null, $whence_index = null)`: append data into the component;
* `prepend($data, $whence = null, $whence_index = null)`: prepend data into the component;
* `remove($data)`: remove data from the component;

The arguments:

* The `$data` argument can be `null`, a valid component string, a object implementing the `__toString` method, an array or a `Traversable` object;
* The `$whence` argument specify the string segment where to include the data;
* The `$whence_index` argument specify the `$whence` index if it is present more than once;
* When using the `remove` method, if the pattern is present multiple times only the first match found is removed 

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
echo $path->getUriComponent(); //will display /bar/leheros/troll
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
