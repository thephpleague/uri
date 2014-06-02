# League.url


[![Build Status](https://travis-ci.org/thephpleague/url.png?branch=master)](https://travis-ci.org/thephpleague/url)
[![Coverage Status](https://coveralls.io/repos/thephpleague/url/badge.png)](https://coveralls.io/r/thephpleague/url)

The League Url package provides simple and intuitive classes and methods to create and manage Urls in PHP. 

This package is compliant with [PSR-2][], and [PSR-4][].

[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md

## Install


You may install the League Url package with Composer (recommended) or manually.

```json
{
    "require": {
        "League\url": "3.*"
    }
}
```


## System Requirements

You need **PHP >= 5.3.0** to use League Url but the latest stable version of PHP is recommended.

## Instantiation

The easiest way to get started is to add `'/path/to/League/url/src'` to your PSR-4 compliant Autoloader. Once added to the autoloader you can easily instantiate your url:

```php
<?php

use League\Url\Factory;
use League\Url\Url;
//Method 1 : from a given string
$url = new Factory::createFromString('http://www.example.com');
$url = new Factory::createFromString('http://www.example.com', Url::PHP_QUERY_RFC3968);

//Method 2: from the current PHP page
$url = Factory::createFromServer($_SERVER); //don't forget to provide the $_SERVER array
// in PHP5.4+ you can directly use PHP internal constant
$url = Factory::createFromServer($_SERVER, PHP_QUERY_RFC3968);
```

`$url` is a valid `League\Url\Url` object. This is the main value object we will be using to manipulate the url.

## Usage

`League\Url` is a Immutable Value Object:

* The object implements the `__toString` method to enable accessing the string representation of the URL;
* Everytime the object needs to return an object or modify a property you return a clone of that object:
	* you can easily manipulating the url with chaining without modifying the original object.
	* you can not modify the object property without notice.

```php
$url = new Factory::createFromString('http://www.example.com');

$url2 = $url->setUser('john')->setPass('doe')->setPort(443)->setScheme('https');
echo $url2; //output https://john:doe@www.example.com:443/
echo $url; //remains http://www.example.com/

$port = $url2->getPort(); //$port is a clone object of the $url2->port private property.
$port->set(80); //
echo (string) $port; //echo 80;
echo $port->getPort()->__toString() // echo 443; 
```

The `League\Url` also implements the `League\Interfaces\EncodingInterface` and provides methods to specify how to encode the query string:

	* `setEncodingType($enc_type)`: set the encoding rule to applied 
	* `getEncodingType()`: get the current encoding rule 

You can specify the encoding type to be used for the query string when using the Factory methods `createFromString` and `createFromServer` or when using the `setEncodingType` method like below:

```php

$url = new Factory::createFromString(
	'http://www.example.com?query=toto+le+heros',
	Url::PHP_QUERY_RFC17328
);

$url2 = $url->setEncodingType(Url::PHP_QUERY_RFC3968);
echo $url2; //output http://www.example.com?query=toto%20le%20heros
echo $url; //remains http://www.example.com?query=toto+le+heros
```
Of note, `$enc_type` value is either `PHP_QUERY_RFC3968` or `PHP_QUERY_RFC17328` but for backward compatibility in PHP 5.3 you can use `EncodingInterface::PHP_QUERY_RFC3968` or `EncodingInterface::PHP_QUERY_RFC17328`.

## Components classes

Except for `encodingType`, everytime you acces a `League\Url\Url` object getter method it will return one of the following component class. All component classes implements the  `League\Interfaces\ComponentInterface` which means that you can interact with the classes with the following public method:

* `set($data)`: set the component data
* `get()`: returns `null` if the class is empty or its string representation
* `__toString()`: return a typecast string representation of the component.
* `getUriComponent()`: return an altered string representation to ease URL representation.

Of note:

* The `$data` argument can be `null`, a valid component string, or an object implementing the `__toString` method;

### The `Component` class

This class manages the `user`, `pass`, `fragment` components. The data provided to the `set` method can be a string representation of the component or `null`.

### The `Port` and `Scheme` classes

These classes manage the URL port and scheme component. They extend the `Component` class and differ on data validation.

## Complex Components Classes

Complex component classes implement the following interfaces:

* `Countable`
* `IteratorAggregate`
* `ArrayAccess`
* `League\Interfaces\ComponentArrayInterface`

The `League\Interfaces\ComponentArrayInterface` extends the `League\Interfaces\ComponentInterface` by adding the following methods:

* `toArray()`: will return an array representation of the component;
* `fetchKeys($value)`: will return an array containing all the offset which contains the given value. If the value is not found the `array` is empty.

### The `Query` class

This class manage the URL query component and implements:
* the `League\Interfaces\QueryInterface` which extends the `League\Interfaces\ComponentArrayInterface` by adding the following method:

	* `modify($data)`: update the component data;

* the `League\Interfaces\EncodingInterface` by providing the following methods:

	* `setEncodingType($enc_type)`: set the encoding rule to applied 
	* `getEncodingType()`: get the current encoding rule 

Of note:

* The `$data` argument can be `null`, a valid component string, a object implementing the `__toString` method, an array or a `Traversable` object;


Example using the `League\Url\Components\Query` object:

```php
use League\Url\Components\Query;

$query = new Query;
$query['foo'] = 'bar';
$query['baz'] = 'troll';
$query['toto'] = 'le heros';
foreach ($query as $offset => $value) {
	echo "$offset => $value".PHP_EOL;
}
//will echo 
// foo => bar
// baz => troll

$found = $query->fetchKeys('troll');
//$found equals array(0 => 'baz')

echo count($query); //will return 3;
echo (string) $query; //will display foo=bar&baz=troll&toto=le+heros;
$query->setEncodingType(Query::PHP_QUERY_RFC3968); //for PHP 5.3
echo (string) $query; //will display foo=bar&baz=troll&toto=le%20heros;
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

*When using the `remove` method, if the pattern is present multiple times only the first match found is removed* 

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

To ease manipulating complex component like the `host`, the `path` and/or the `query` the following methods where added to `League\Url\Url`:

* `appendHost($data, $whence = null, $whence_index = null)`
* `prependHost($data, $whence = null, $whence_index = null)`
* `removeHost($data)`
* `appendPath($data, $whence = null, $whence_index = null)`
* `prependPath($data, $whence = null, $whence_index = null)`
* `removePath($data)`
* `modifyQuery($data)`

These methods are proxies to the internal component method but return a full clone `League\Url\Url` object. 

```php
$url3 = $url2->modifyQuery(array('query' => 'value'));
echo $url3 //output https://john:doe@www.example.com:443/?query=value
echo $url2; //remains https://john:doe@www.example.com:443/

//You could do the same using the following logic.

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

Please see [CONTRIBUTING](https://github.com/thephpleague/url/blob/master/CONTRIBUTING.md) for details.

Credits
-------

- [ignace nyamagana butera](https://github.com/nyamsprod)
- [All Contributors](https://github.com/thephpleague/url/graphs/contributors)