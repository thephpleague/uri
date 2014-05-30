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

use League\Url\Factory as Url;
use League\Url\Interfaces\QueryInterface; //For PHP 5.3 backward compatibility

//Method 1 : from a given string
$url = new Url::createFromString('http://www.example.com');
$url = new Url::createFromString('http://www.example.com', QueryInterface::PHP_QUERY_RFC3968);

//Method 2: from the current PHP page
$url = Url::createFromServer($_SERVER); //don't forget to provide the $_SERVER array
// in PHP5.4+ you can directly use PHP internal constant
$url = Url::createFromServer($_SERVER, PHP_QUERY_RFC3968);
```

`$url` is a valid `League\Url\Url` object. This is the main value object we will be using to manipulate the url.

## Usage

`League\Url` is a Immutable Value Object:

* The object implements the `__toString` method to enable accessing the string representation of the URL;
* Everytime the object needs to return an object or modify a property you return a clone of that object:
	* you can easily manipulating the url with chaining without modifying the original object.
	* you can not modify the object property without notice.

```php
$url = new Url::createFromString('http://www.example.com');

$url2 = $url->setUser('john')->setPass('doe')->setPort(443)->setScheme('https');
echo $url2; //output https://john:doe@www.example.com:443/
echo $url; //remains http://www.example.com/

$port = $url2->getPort(); //$port is a clone object of the $url2->port private property.
$port->set(80); //
echo (string) $port; //echo 80;
echo $port->getPort()->__toString() // echo 443; 
```

You can specify the encoding type to be used for the query string when using the Factory methods `createFromString` and `createFromServer` or when using the `setEncodingType` method like below:

```php

use `League\Url\Interface\QueryInterface`;

$url = new Url::createFromString(
	'http://www.example.com?query=toto+le+heros',
	QueryInterface::PHP_QUERY_RFC17328
);

$url2 = $url->setEncodingType(QueryInterface::PHP_QUERY_RFC3968);
echo $url2; //output http://www.example.com?query=toto%20le%20heros
echo $url; //remains http://www.example.com?query=toto+le+heros
```

To ease manipulating complex component like the `host`, the `path` and/or the `query` the following methods where added:

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

*When using the `removePath` and the `removeHost` methods, if the pattern is present multiple times only the first match found is removed* 

```php
$url3 = $url2->modifyQuery(['query' => 'value']);
echo $url3 //output https://john:doe@www.example.com:443/?query=value
echo $url2; //remains https://john:doe@www.example.com:443/
```

## Components classes

Except for `encodingType`, everytime you acces a `League\Url\Url` object getter method it will return one of the following component class. Each `League\Url\Url` object is composed of 8 URL components object composed of 4 classes. 

All component classes implements the  `League\Interfaces\ComponentInterface` which means that you can interact with the classes with the following public method:

* `set($data)`: set the component data
* `get()`: returns null if the class is empty or its string representation
* `__toString`: return the string representation of the component if the data is null then the method output an empty string.
* `getUriComponent`: return an alter string representation to ease URL representation.

### The `Component` class

This class manages the `user`, `pass`, `fragment` components. The data provided to the `set` method can be a string representation of the component or `null`.

### The `Port` and `Scheme` classes

These classes manage the URL port and scheme component. They extend the `Component` class and only differ when validating the object data.

## Complex Components Classes

Complex component classes implement the following interfaces:

* `Countable`
* `IteratorAggregate`
* `ArrayAccess`
* `League\Interfaces\ComponentArrayInterface`

The `League\Interfaces\ComponentArrayInterface` extends the `League\Interfaces\ComponentInterface` by adding the following methods:

* `toArray`: will return an array representation of the component;
* `contains`: will detect the offset of a given value or return `null` if the value was not found in the component. Of note: If the value appears more that once, only the first offset found will be return.

### The `Query` class

This class manage the URL query component and implements the `League\Interfaces\QueryInterface` which extends the `League\Interfaces\ComponentArrayInterface` by adding the following method:

* `modify($data)`: update the component data;

Example using the `League\Url\Components\Query` object:

```php
use League\Url\Components\Query;

$query = new Query;
$query['foo'] = 'bar';
$query['baz'] = 'troll';
foreach ($query as $offset => $value) {
	echo "$offset => $value".PHP_EOL;
}
//will echo 
// foo => bar
// baz => troll

$offset = $query->contains('troll');
//$offset equels 'baz'

echo count($query); //will return 2;
echo (string) $query; //will display foo=bar&baz=troll;
```

### The `Path` and `Host` classes

These classes manage the URL port and schemes component. They only differs in the way they validate the data they receive. Both classes implements the `League\Interfaces\SegmentInterface` which extends the `League\Interfaces\ComponentArrayInterface` by adding the following methods:

* `append($data, $whence = null, $whence_index = null)`: append data into the component;
* `prepend($data, $whence = null, $whence_index = null)`: prepend data into the component;
* `remove($data)`: remove data from the component;

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

$offset = $path->contains('troll');
//$offset equels '2'

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