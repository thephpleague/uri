---
layout: default
title: The Query component
---

The Query
=======

The library provides a `Query` class to ease query string creation and manipulation. This URI component object exposes the [package common API](/5.0/components/api/), but also provide specific methods to work with the URI query component.

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">When a modification fails an <code>InvalidArgumentException</code> exception is thrown.</p>

## Query as a PHP data transport layer

### Query::extract

~~~php
<?php

public static Query::extract(string $query[, string $separator = '&' [, int $enc_type = ComponentInterface::RFC3986_ENCODING]]): array
~~~

This method deserializes the query string parameters into an associative `array` similar to PHP's `parse_str` when used with its optional second argument. The methods expects the following arguments:

- The query string **required**;
- The query string separator **optional**, by default it is set to `&`;
- The query string encoding. One of the `ComponentInterface` encoding type constant.

The main differences are with `parse_str` usage are the following:

- `Query::extract` accepts a parameter which describes the query string separator
- `Query::extract` does not mangle the query string data.
- `Query::extract` is not affected by PHP `max_input_vars`.

~~~php
<?php

use League\Uri\Components\Query;

$query_string = 'foo.bar=bar&foo_bar=baz';
parse_str($query_string, $out);
var_export($out);
// $out = ["foo_bar" => 'baz'];

$arr = Query::extract($query_string);
// $arr = ['foo.bar' => 'bar', 'foo_bar' => baz'];
~~~

### Query::getParams

If you already have an instantiated `Query` object you can return all the query string deserialized arguments using the `Query::getParams` method:

~~~php
<?php

use League\Uri\Components\Query;

$query_string = 'foo.bar=bar&foo_bar=baz';
parse_str($query_string, $out);
var_export($out);
// $out = ["foo_bar" => 'baz'];

$arr = (new Query($query_string))->getParams();
// $arr = ['foo.bar' => 'bar', 'foo_bar' => baz']];
~~~

### Query::getParam

If you are only interested in a given argument you can access it directly using the `Query::getParam` method as show below:

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo[]=bar&foo[]=y+olo&z=');
$query->getParam('foo');          //return ['bar', 'y+olo']
$query->getParam('gweta', 'now'); //return 'now'
~~~

The method returns the value of a specific argument. If the argument does not exist it will return the value specified by the second argument which defaults to `null`.

## Query as a collection of key/value pairs

~~~php
<?php

public static function Query::parse(string $query_string [, string $separator = '&' [, int $enc_type = self::RFC3986_ENCODING]]): array
public static function Query::build(array $pairs [, string $separator = '&' [, int $enc_type = self::RFC3986_ENCODING]]): string
public static function Query::createFromPairs(iterable $pairs): self
public function Query::getIterator(): ArrayIterator
public function Query::count(): int
public function Query::getPairs(): array
public function Query::getPair(string $offset, $default = null): mixed
public function Query::hasPair(string $offset): bool
public function Query::keys([mixed $value = null]): array
~~~

This class mainly represents the query string as a collection of key/value pairs.

### Query::parse

This method parse the query string into an associative `array` of key/values pairs. The method expects three (3) arguments:

- The query string **required**;
- The query string separator **optional**, by default it is set to `&`;
- The query string encoding. One of the `ComponentInterface` encoding type constant.

The value returned for each pair can be:

- `null`,
- a `string`
- an array of `string` and/or `null` values.

~~~php
<?php

use League\Uri\Components\Query;

$query_string = 'toto.foo=bar&toto.foo=baz&foo&baz=';
$arr = Query::parse($query_string, '&');
// [
//     "toto.foo" => [["bar", "baz"],
//     "foo" => null,
//     "baz" => "",
// ]
~~~

### Query::build

The `Query::build` restore the query string from the result of the `Query::parse` method. The method expects at most 3 arguments:

- A valid `array` of key/value pairs to convert;
- The query string separator, by default it is set to `&`;
- The query string encoding using one of the `ComponentInterface` constant

<p class="message-info">By default the encoding is set to <code>ComponentInterface::RFC3986_ENCODING</code></p>

~~~php
<?php

use League\Uri\Components\Query;

$query_string = 'foo[]=bar&foo[]=baz';
$arr = Query::parse($query_string, '&', Query::RFC3986_ENCODING);
var_export($arr);
// $arr include the following data ["foo[]" => ['bar', 'baz']];

$res = Query::build($arr, '&', false);
// $res = 'foo[]=bar&foo[]=baz'
~~~

### Query::createFromPairs

Returns a new `Query` object from an `array` or a `Traversable` object.

* `$pairs` : The submitted data must be an `array` or a `Traversable` key/value structure similar to the result of [Query::parse](#parsing-the-query-string-into-an-array).

#### Examples

~~~php
<?php

use League\Uri\Components\Query;

$query =  Query::createFromPairs([
    'foo' => 'bar',
    'p' => 'yolo',
    'z' => ''
]);
echo $query; //display 'foo=bar&p=yolo&z='

$query =  Query::createFromPairs([
    'foo' => 'bar',
    'p' => null,
    'z' => ''
]);
echo $query; //display 'foo=bar&p&z='
~~~

### Countable and IteratorAggregate

The class implements PHP's `Countable` and `IteratorAggregate` interfaces. This means that you can count the number of pairs and use the `foreach` construct to iterate over them.

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
count($query); //return 4
foreach ($query as $key => $value) {
    //do something meaningful here
}
~~~

<p class="message-info">When looping the key and the value are decoded.</p>

### Query::getPairs

The `Query::getPairs` method returns the object's array representation.

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
$query->getPairs();
// returns [
//     'foo' => 'bar',
//     'p'   => 'y olo',
//     'z'   => '',
// ]
~~~

<p class="message-info">The returned array contains decoded data.</p>

### Query::getPair

If you are only interested in a given pair you can access it directly using the `Query::getPair` method as show below:

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
$query->getPair('foo');          //return 'bar'
$query->getPair('gweta');        //return null
$query->getPair('gweta', 'now'); //return 'now'
~~~

The method returns the value of a specific pair key. If the key does not exist it will return the value specified by the second argument which defaults to `null`.

<p class="message-info">The returned data are fully decoded.</p>

### Query::hasPair

Because a pair value can be `null` the `Query::hasPair` method is used to remove the possible `Query::getPair` result ambiguity.

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p&z=');
$query->getPair('foo');   //return 'bar'
$query->getPair('p');     //return null
$query->getPair('gweta'); //return null

$query->hasPair('gweta'); //return false
$query->hasPair('p');     //return true
~~~

### Query::keys

If you are interested in getting the pairs keys you can do so using the `Query::keys` method.

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
$query->keys();        //return ['foo', 'p', 'z'];
$query->keys('bar');   //return ['foo'];
$query->keys('gweta'); //return [];
~~~

By default, the method returns all the keys, but if you supply a value, only the keys whose value equals the value are returned.

## Manipulating the query

~~~php
<?php

public function Query::merge(string $query): self
public function Query::append(string $query): self
public function Query::ksort(mixed $sort): self
public function Query::withoutPairs(array $offsets): array
~~~

### Query::merge

`Query::merge` returns a new `Query` object with its data merged.

~~~php
<?php

public function Query::merge(string $query): Query
~~~

This method expects a single argument which is a string

~~~php
<?php

use League\Uri\Components\Query;

$query    = new Query('foo=bar&baz=toto');
$newQuery = $query->merge('foo=jane&r=stone');
$newQuery->__toString(); //return foo=jane&baz=toto&r=stone
// the 'foo' parameter was updated
// the 'r' parameter was added
~~~

<p class="message-info">Values equal to <code>null</code> or the empty string are merge differently.</p>

~~~php
<?php

use League\Uri\Components\Query;

$query    = Query::createFromPairs(['foo' => 'bar', 'baz' => 'toto']);
$newQuery = $query->merge('baz=&r');
$newQuery->__toString(); //return foo=bar&baz=&r
// the 'r' parameter was added without any value
// the 'baz' parameter was updated to an empty string and its = sign remains
~~~

### Query::append

`Query::append` returns a new `Query` object with its data append to it.

~~~php
<?php

public function Query::append(string $query): Query
~~~

This method expects a single argument which is a string


~~~php
<?php

use League\Uri\Components\Query;

$query    = new Query('foo=bar&john=doe');
$newQuery = $query->append('foo=baz');
$newQuery->__toString(); //return foo=jane&foo=baz&john=doe
// a new foo parameter is added
~~~

### Query::withoutPairs

`Query::withoutPairs` returns a new `Query` object with deleted pairs according to their keys.

This method expects an array containing a list of keys to remove as its single argument.

~~~php
<?php

use League\Uri\Components\Query;

$query    = new Query('foo=bar&p=y+olo&z=');
$newQuery = $query->withoutPairs(['foo', 'p']);
echo $newQuery; //displays 'z='
~~~

### Query::ksort

`Query::ksort` returns a `Query` object with its pairs sorted according to its keys or a user defined function.

The single argument `sort` can be:

One of PHP's sorting constant used by the [sort function](http://php.net/sort). **In this case the query parameters are sorted from low to high** like PHP's [ksort function](http://php.net/ksort)

~~~php
<?php

use League\Uri\Components\Query;

$query    = new Query('foo=bar&baz=toto');
$newQuery = $query->ksort(SORT_STRING);
$newQuery->__toString(); //return baz=toto&foo=bar
~~~

A user-defined comparison function which must return an integer less than, equal to, or greater than zero if the first argument is considered to be respectively less than, equal to, or greater than the second, like PHP's [uksort function](http://php.net/uksort)

~~~php
<?php

use League\Uri\Components\Query;


$query    = new Query('foo=bar&baz=toto');
$newQuery = $query->ksort('strcmp');
$newQuery->__toString(); //return baz=toto&foo=bar
~~~