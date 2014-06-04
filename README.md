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
use League\Url\Interfaces\EncodingInterface; //needed only in PHP5.3
//Method 1 : from a given string
$url = new Factory::createFromString('http://www.example.com');
$url = new Factory::createFromString('http://www.example.com', EncodingInterface::PHP_QUERY_RFC3968);

//Method 2: from the current PHP page
$url = Factory::createFromServer($_SERVER); //don't forget to provide the $_SERVER array
// in PHP5.4+ you can directly use PHP internal constant
$url = Factory::createFromServer($_SERVER, PHP_QUERY_RFC3968);
```

`$url` is a valid `League\Url\Url` object. This is the main value object we will be using to manipulate the url.

## Usage

### Immutable Object

`League\Url` is a Immutable Value Object:

* The object implements the `__toString` method to enable accessing the string representation of the URL;
* Everytime the object needs to return or to modify a property you return a clone of the modified object or of the property object:
	* you can easily manipulating the url with chaining without modifying the original object.
	* you can not modify the object property without notice.

```php
$url = new Factory::createFromString('http://www.example.com');
$url2 = $url
		->setUser('john')
		->setPass('doe')
		->setPort(443)
		->setScheme('https');
echo $url2; //output https://john:doe@www.example.com:443/
echo $url; //remains http://www.example.com/

$port = $url2->getPort(); //$port is a clone object of the $url2->port private property.
$port->set(80); //
echo (string) $port; //echo 80;
echo $port->getPort()->__toString() // echo 443; 
```
### Parsing the URL

Once created, the object can return its components using the `parse` method. This methods returns an associated array similar to php `parse_url` returned object. 

```php
$url = new Factory::createFromString('http://www.example.com?foo=bar');
var_export($url->parse()); 
// will output the following array:
// array(
//     'scheme' => 'http',
//     'user' => null,
//     'pass' => null,
//     'host' => 'www.example.com',
//     'path' => null,
//     'query' => 'foo=bar',
//     'fragment' => null,
// );
```

### Setting URL Query component encoding style

The `League\Url\Url` implements the `League\Interfaces\EncodingInterface`, this interface provides methods and constant values to specify how to encode the query string:
* `EncodingInterface::PHP_QUERY_RFC3968` constant specify to encode the query following the RFC #3968
* `EncodingInterface::PHP_QUERY_RFC1738` constant specify to encode the query following the RFC #1738
* `setEncodingType($enc_type)`: set the encoding constant
* `getEncodingType()`: get the current encoding constant used

You can specify the encoding type to be used for the URL query string with the following methods:

* the `League\Url\Factory::createFromString($url, $enc_type = EncodingInterface::PHP_QUERY_RFC1738)`
* the `League\Url\Factory::createFromServer(array $server, $enc_type = EncodingInterface::PHP_QUERY_RFC1738)`
* the `League\Url\Url::setEncodingType($enc_type)`

```php

$url = new Factory::createFromString(
	'http://www.example.com?query=toto+le+heros',
	EncodingInterface::PHP_QUERY_RFC17328 // in PHP 5.3
);

$url2 = $url->setEncodingType(PHP_QUERY_RFC3968); // in PHP 5.4+
echo $url2; //output http://www.example.com?query=toto%20le%20heros
echo $url; //remains http://www.example.com?query=toto+le+heros
```
Of note, `$enc_type` value is either `PHP_QUERY_RFC3968` or `PHP_QUERY_RFC17328` but for backward compatibility in PHP 5.3 you can use `EncodingInterface::PHP_QUERY_RFC3968` or `EncodingInterface::PHP_QUERY_RFC17328`.

### Url output

To get the string representation of the given URL you need to invoke the `__toString()` method. But note that for Url without path a `/` representing the default path will be added if needed.

```php
$url = new Factory::createFromString('http://www.example.com#fragment');
echo (string) $url; //will output 'http://www.example.com/#fragment' notice the trailing slash added
```

### Comparing `League\Url\Url` objects

To enable object comparison we have a `League\Url\Url::sameValueAs` method which can behave in strict or non strict mode. In strict mode the encoding type used for the query string representation is taken into account.
```php
    $url1 = Factory::createFromString('example.com');
    $url2 = Factory::createFromString('//example.com');
    $url3 = Factory::createFromString('//example.com?foo=toto+le+heros', Query::PHP_QUERY_RFC3986);
    $url4 = Factory::createFromString('//example.com?foo=toto+le+heros');
    $url1->sameValueAs($url2); //will return true
    $url3->sameValueAs($url2); //will return false
    $url3->sameValueAs($url4); //will return true
    $url3->sameValueAs($url4, true); //will return false <- this is a strict comparaison
```

## URL components classes

Except for the `League\Url\Url::encodingType` property, everytime you acces a `League\Url\Url` getter method it return a clone of the given property class. 

For each URL component exists a component class that implements the `League\Interfaces\ComponentInterface` so each class has the following public methods:

* `set($data)`: set the component data
* `get()`: returns `null` if the class data is empty or its string representation
* `__toString()`: return a typecast string representation of the component.
* `getUriComponent()`: return an altered string representation to ease URL representation.

Of note:

* The `$data` argument can be:
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
The URL components that only implement this interface are:

* `scheme` with the `League\Url\Components\Scheme`;
* `user` with the `League\Url\Components\User`;
* `pass` with the `League\Url\Components\Pass`;
* `port` with the `League\Url\Components\Port`;
* `fragment` with the `League\Url\Components\Fragment`;

The classes differ on how they validate the data and/or on how they format the component string.

## Complex Components Classes

Classes that deal with Url complex component (ie: `host`, `path`, `query`) implement the following interfaces:

* `Countable`
* `IteratorAggregate`
* `ArrayAccess`
* `League\Interfaces\ComponentArrayInterface`

The `League\Interfaces\ComponentArrayInterface` extends the `League\Interfaces\ComponentInterface` by adding the following methods:

* `toArray()`: will return an array representation of the component;
* `fetchKeys($value)`: will return an array containing all the offset which contains the given value. If the value is not found then an empty `array` is returned.

*Of note: The `$data` argument for the `set` method can also be an `array` or a `Traversable` object.*

### The `Query` class

This class manage the URL query component and implements the following interfaces:

* the `League\Interfaces\EncodingInterface`;
* the `League\Interfaces\QueryInterface` which extends the `League\Interfaces\ComponentArrayInterface` by adding the following method:

	* `modify($data)`: update the component data;

By default, the `Query` class encode its members using the RFC #1738

Example using the `League\Url\Components\Query` object:

```php
use League\Url\Components\Query;

$query = new Query('foo=bar', PHP_QUERY_RFC1738); //in PHP5.4+
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

$found = $query->fetchKeys('troll');
//$found equals array(0 => 'baz')

echo count($query); //will return 2;
echo (string) $query; //will display foo=baz&baz=troll;
$query->setEncodingType(Query::PHP_QUERY_RFC3968); //for PHP 5.3
$query->modify(array('toto' => 'le gentil'));
echo (string) $query; //will display foo=baz&baz=troll&toto=le%20gentil;
```

### The `Path` and `Host` classes

These classes manage the URL path and host components. They only differs in the way they validate their data. Both classes implements the `League\Interfaces\SegmentInterface` which extends the `League\Interfaces\ComponentArrayInterface` by adding the following methods:

* `append($data, $whence = null, $whence_index = null)`: append data into the component;
* `prepend($data, $whence = null, $whence_index = null)`: prepend data into the component;
* `remove($data)`: remove data from the component;

Of note:

* The `$data` argument can be `null`, a valid component string, a object implementing the `__toString` method, an array or a `Traversable` object;
* The `$whence` argument specify where to include the appended data;
* The `$whence_index` argument specify the `$whence` index if it is present more than once in the object;
* When using the `remove` method, if the pattern is present multiple times only the first match found is removed* 

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

$found = $path->fetchKeys('troll');
//$found equals array(0 => '2');

echo count($path); //will return 4;
echo (string) $path; //will display bar/leheros/troll/troll
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

## Manipulating URL components

In addition to the setter method for each URL component and for the URL query encoding type, the following methods where added to `League\Url\Url` To ease manipulating complex component like the `host`, the `path` and/or the `query` :

* `appendHost($data, $whence = null, $whence_index = null)`
* `prependHost($data, $whence = null, $whence_index = null)`
* `removeHost($data)`
* `appendPath($data, $whence = null, $whence_index = null)`
* `prependPath($data, $whence = null, $whence_index = null)`
* `removePath($data)`
* `modifyQuery($data)`

These methods are proxies to the internal component method but like all setters, they return a fully clone `League\Url\Url` object. 

```php
$url3 = $url2->modifyQuery(array('query' => 'value'));
echo $url3 //output https://john:doe@www.example.com:443/?query=value
echo $url2; //remains https://john:doe@www.example.com:443/

//is equivalent to:

$query = $url2->getQuery();
$query->modify(array('query' => 'value'));
$url3 = $url2->setQuery($query);

echo $url3 //output https://john:doe@www.example.com:443/?query=value
echo $url2; //remains https://john:doe@www.example.com:443/

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
