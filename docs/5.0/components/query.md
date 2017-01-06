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

### Query::getParams

The `Query::getParams` method returns the query string deserialized argument if any in an associative `array` similar to PHP's `parse_str` when used with its optional second argument. The main differences are with `parse_str` usage are the following:

- `Query::getParams` does not mangle the query string data.
- `Query::getParams` is not affected by PHP `max_input_vars`.

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

<p class="message-info">In PHP7 using the coalescence operator <code>??</code> you can quicky use a default value if the parameter does not exists.</p>

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&baz=yes');
$default_value = 'test';

$param1 = $query->getParams()['non_existing_key'] ?? $default_value;
echo $param1; //display 'test'

$param2 = $query->getParams()['foo'] ?? $default_value;
echo $param2; //display 'bar'
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

<p class="message-notice">This method is used by the URI modifier <code>MergeQuery</code></p>

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

<p class="message-notice">This method is used by the URI modifier <code>AppendQuery</code></p>


## Query as a collection of key/value pairs

This class mainly represents the query string as a collection of key/value pairs to preserve the query content.

### Creating a query object from a collection of pairs

#### Query::createFromPairs

~~~php
<?php

public static function Query::createFromPairs(iterable $pairs): Query
~~~

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

- If a given parameter value is `null` it will be rendered without any value in the resulting query string;
- If a given parameter value is an empty string il will be rendered without any value but with a `=` sign appended to it;

### Accessing the query pairs

~~~php
<?php

public function Query::getIterator(): ArrayIterator
public function Query::count(): int
public function Query::getPairs(): array
public function Query::getPair(string $offset, $default = null): mixed
public function Query::has(string $offset): bool
public function Query::keys([mixed $value = null]): array
~~~

#### Countable and IteratorAggregate

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

#### Query::getPairs

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

#### Query::getPair

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

#### Query::has

Because a pair value can be `null` the `Query::has` method is used to remove the possible `Query::getPair` result ambiguity.

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p&z=');
$query->getPair('foo');   //return 'bar'
$query->getPair('p');     //return null
$query->getPair('gweta'); //return null

$query->has('gweta'); //return false
$query->has('p');     //return true
~~~

#### Query::keys

If you are interested in getting the pairs key you can do so using the `Query::keys` method.

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
$query->keys();        //return ['foo', 'p', 'z'];
$query->keys('bar');   //return ['foo'];
$query->keys('gweta'); //return [];
~~~

By default, the method returns all the keys, but if you supply a value, only the keys whose value equals the value are returned.

### Manipulating the query pairs

#### Sorting the query pairs

`Query::ksort` returns a `Query` object with its pairs sorted according to its keys or a user defined function.

~~~php
<?php

public function Query::ksort(mixed $sort): Query
~~~

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

<p class="message-notice">This method is used by the URI modifier <code>KsortQuery</code></p>

#### Removing pairs

`Query::delete` returns a new `Query` object with deleted pairs according to their keys.

~~~php
<?php

public function Query::delete(string[] $keys): Query
~~~

This method expects an array containing a list of keys to remove as its single argument.

~~~php
<?php

use League\Uri\Components\Query;

$query    = new Query('foo=bar&p=y+olo&z=');
$newQuery = $query->delete(['foo', 'p']);
echo $newQuery; //displays 'z='
~~~

<p class="message-notice">This method is used by the URI modifier <code>RemoveQueryKeys</code></p>

## Raw manipulations

The library does not rely on PHP’s `parse_str` and `http_build_query` functions. Instead, the `Query` object exposes public static methods to parse, build and extract data from the query string.

### Parsing the query string

~~~php
<?php

public static Query::parse(string $query_string [, string $separator = '&' [, int $enc_type = ComponentInterface::RFC3986_ENCODING]]): array
~~~

This method parse the query string into an associative `array` of key/pairs value. The methods expects two arguments:

- The query string **required**;
- The query string separator **optional**, by default it is set to `&`;
- The query string encoding. One of the `ComponentInterface` encoding type constant.

    - `ComponentInterface::RFC1738_ENCODING`
    - `ComponentInterface::RFC3986_ENCODING`
    - `ComponentInterface::RFC3987_ENCODING`
    - `ComponentInterface::NO_ENCODING`

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
//     "baz" => ""]
// ]
~~~

### Building the query string

~~~php
<?php

public static Query::build(array $data [, string $separator = '&' [, int $encoding = RFC3986]]): string
~~~

The `Query::build` restore the query string from the result of the `Query::parse` method. The method expects at most 3 arguments:

- A valid `array` of data to convert;
- The query string separator, by default it is set to `&`;
- The query string encoding. It can be:
    - `ComponentInterface::RFC1738_ENCODING`
    - `ComponentInterface::RFC3986_ENCODING`
    - `ComponentInterface::RFC3987_ENCODING`
    - `ComponentInterface::NO_ENCODING` if you don't want any encoding.

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

### Extracting PHP's variables from the query string

~~~php
<?php

public static Query::extract(string $query[, string $separator = '&' [, int $enc_type = ComponentInterface::RFC3986_ENCODING]]): array
~~~

This method deserializes the query string parameters into an associative `array` similar to PHP's `parse_str` when used with its optional second argument. The methods expects the following arguments:

- The query string **required**;
- The query string separator **optional**, by default it is set to `&`;
- The query string encoding. One of the `ComponentInterface` encoding type constant.

    - `ComponentInterface::RFC1738_ENCODING`
    - `ComponentInterface::RFC3986_ENCODING`
    - `ComponentInterface::RFC3987_ENCODING`
    - `ComponentInterface::NO_ENCODING`

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
// $arr = ['foo.bar' => 'bar', 'foo_bar' => baz']];
~~~

