---
layout: default
title: The Query component
---

The Query
=======

The library provides a `Query` class to ease complex query manipulation. The object implements:

- the `League\Uri\Components\ComponentInterface` Interface
- PHP's `Countable` Interface
- PHP's `IteratorAggregate` Interface

## Query creation

### Using a named constructor

Returns a new `Query` object from an `array` or a `Traversable` object.

~~~php
<?php

public static function Query::createFromPairs($pairs): Query
~~~

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

## Properties and methods

The component representation, comparison and manipulation is done using the package [Component](/dev-master/components/api/) interfaces methods.

## Accessing query pairs

~~~php
<?php

public function Query::getIterator(): ArrayIterator
public function Query::count(): int
public function Query::getPairs(): array
public function Query::keys(mixed $value = null): array
public function Query::getValue(string $offset, $default = null): mixed
~~~

### Countable and IteratorAggregate

The class provides several methods to work with its parameters. The class implements PHP's `Countable` and `IteratorAggregate` interfaces. This means that you can count the number of parameters and use the `foreach` construct to iterate over them.

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
count($query); //return 4
foreach ($query as $key => $value) {
    //do something meaningful here
}
~~~

<p class="message-info">When looping the returned data are decoded.</p>

### Query::getPairs

A query can be represented as an array of its internal key/value pairs. Through the use of the `Query::getPairs` method the class returns the object's array representation. This method uses `Query::parse` to create the array.

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

<p class="message-warning">The array returned by <code>getPairs</code> differs from the one returned by <code>parse_str</code> as it <a href="/services/parser-query/">preserves the query string values</a>.</p>

### Query::keys

Returns the decoded query keys as shown below:

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
$query->keys();        //return ['foo', 'p', 'z'];
$query->keys('bar');   //return ['foo'];
$query->keys('gweta'); //return [];
~~~

By default, the method returns all the keys names, but if you supply a value, only the keys whose value equals the value are returned.

### Query::getValue

Returns the decoded query value of a specified pair key as shown below:

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
$query->getValue('foo');          //return 'bar'
$query->getValue('gweta');        //return null
$query->getValue('gweta', 'now'); //return 'now'
~~~

The method returns the value of a specific parameter name. If the offset does not exist it will return the value specified by the second argument which defaults to `null`.

## Modifying a query

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">When a modification fails an <code>InvalidArgumentException</code> is thrown.</p>

### Query::ksort

Returns a `Query` object with its pairs sorted according to its keys.

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

### Query::merge

Returns a new `Query` object with its data merged.

~~~php
<?php

public function Query::merge(?string $query): Query
~~~

This method expects a single argument which can be a string or `null`:

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

<p class="message-info">This method is used by the URI modifier <code>MergeQuery</code></p>

### Query::without

Returns a new `Query` object with deleted pairs according to their keys.

~~~php
<?php

public function Query::without(string[] $keys): Query
~~~

This method expects an array containing a list of keys to remove as its single argument.

~~~php
<?php

use League\Uri\Components\Query;

$query    = new Query('foo=bar&p=y+olo&z=');
$newQuery = $query->without(['foo', 'p']);
echo $newQuery; //displays 'z='
~~~

<p class="message-notice">This method is used by the URI modifier <code>RemoveQueryKeys</code></p>

### Query::filter

Returns a new `Query` object with filtered pairs.

~~~php
<?php

public function Query::filter(callable $filter, $flag = 0): Query
~~~

Filtering is done using the same arguments as PHP's `array_filter`.

You can filter the query by the pairs values:

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
$filter = function ($value) {
    return !empty($value);
};

$newQuery = $query->filter($filter);
echo $newQuery; //displays 'foo=bar&p=y+olo'
~~~

You can filter the query by the pairs keys:

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
$filter = function ($key) {
    return strpos($key, 'f');
};

$newQuery = $query->filter($filter, ARRAY_FILTER_USE_KEY);
echo $newQuery; //displays 'foo=bar'
~~~

You can filter the query by pairs

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('toto=foo&bar=foo&john=jane');
$filter = function ($value, $key) {
    return (strpos($value, 'o') !== false && strpos($key, 'o') !== false);
};

$newQuery = $query->filter($filter, ARRAY_FILTER_USE_BOTH);
echo $newQuery; //displays 'toto=foo'
~~~

By specifying the second argument flag you can change how filtering is done:

- use `0` to filter by pairs value;
- use `ARRAY_FILTER_USE_KEY` to filter by pairs key;
- use `ARRAY_FILTER_USE_BOTH` to filter by pairs value and key;

By default, the flag value is `0`, just like with `array_filter`.

<p class="message-notice">This method is used by the URI modifier <code>FilterQuery</code></p>

Manipulating the Query string
---------

To preserve the query string, the library does not rely on PHP's `parse_str` and `http_build_query` functions.

Instead, the class provides:
- a static method that can be used to parse a query string into an array of key value pairs.
- a static method to build a query string from an array of key value pairs
- a static method to extract PHP's variable from a query string.

### Parsing the query string into an array

~~~php
<?php

public Query::parse(string $query_string [, string $separator = '&' [, int $encoding = RFC3986_ENCODING]]): array
~~~

The `Query::parse` method returns an `array` representation of the query string which preserve key/value pairs. The method expects at most 3 arguments:

- The query string;
- The query string separator, by default it is set to `&`;
- The query string encoding. It can be:
    - `ComponentInterface::RFC3986_ENCODING`
    - `ComponentInterface::RFC3987_ENCODING`
    - `ComponentInterface::NO_ENCODING` if you don't want any encoding.

<p class="message-info">By default the encoding is set to <code>ComponentInterface::RFC3986_ENCODING</code></p>

~~~php
<?php

use League\Uri\Components\Query;

$query_string = 'toto.foo=bar&toto.foo=baz';
$arr = Query::parse($query_string, '&', PHP_RFC3986);
// $arr is an array containing ["toto.foo" => [["bar", "baz"]]
~~~

#### Main differences with `parse_str`:

- `parse_str` replaces any invalid characters from the query string pair key that can not be included in a PHP variable name by an underscore `_`.
- `parse_str` merges query string values.

These behaviors, specific to PHP, may be considered to be a data loss transformation in other languages.

~~~php
<?php

$query_string = 'toto.foo=bar&toto.foo=baz';
parse_str($query_string, $arr);
// $arr is an array containing ["toto_foo" => "baz"]
~~~

### Building the query string from an array

~~~php
<?php

public Query::build(array $data [, string $separator = '&' [, int $encoding = RFC3986]]): string
~~~

The `Query::build` public static method returns and preserves string representation of the query string from the `Query::parse` array result. The method expects at most 3 arguments:

- A valid `array` of data to convert;
- The query string separator, by default it is set to `&`;
- The query string encoding. It can be:
    - `ComponentInterface::RFC3986_ENCODING`
    - `ComponentInterface::RFC3987_ENCODING`
    - `ComponentInterface::NO_ENCODING` if you don't want any encoding.

<p class="message-info">By default the encoding is set to <code>ComponentInterface::RFC3986_ENCODING</code></p>

~~~php
<?php

use League\Uri\Components\Query;

$query_string = 'foo[]=bar&foo[]=baz';
$arr = Query::parse($query_string, '&', PHP_RFC3986);
var_export($arr);
// $arr include the following data ["foo[]" => ['bar', 'baz']];

$res = Query::build($arr, '&', false);
// $res = 'foo[]=bar&foo[]=baz'
~~~

#### Main differences with `http_build_query`:

`http_build_query` always adds array numeric prefix to the query string even when they are not needed

using PHP's `parse_str`

~~~php
<?php

$query_string = 'foo[]=bar&foo[]=baz';
parse_str($query_string, $arr);
// $arr = ["foo" => ['bar', 'baz']];

$res = rawurldecode(http_build_query($arr, '', PHP_QUERY_RFC3986));
// $res equals foo[0]=bar&foo[1]=baz
~~~

or using `Query::parse`

~~~php
<?php

use League\Uri\Components\Query;

$query_string = 'foo[]=bar&foo[]=baz';
$arr = Query::parse($query_string, '&', PHP_RFC3986);
// $arr = ["foo[]" => ['bar', 'baz']];

$res = rawurldecode(http_build_query($arr, '', PHP_QUERY_RFC3986));
// $res equals foo[][0]=bar&oo[][1]=baz
~~~

### Extracting PHP's variable from the query string

~~~php
<?php

public static Query::extract(string $query[, string $separator = '&']): array
~~~

The `Query::extract` method returns an `array` representation of the query string similar to PHP's `parse_str` used with its optional second argument. The main difference are:

- `Query::extract` accepts a second parameter which describe the query string separator
- `Query::extract` does not mangle the query string data.

~~~php
<?php

use League\Uri\Components\Query;

$query_string = 'foo.bar=bar&foo_bar=baz';
parse_str($query_string, $out);
var_export($out);
// $out = ["foo_bar" => 'baz'];

$arr = Query::extract($query_string);
// $arr = ["foo.bar" => 'bar', 'foo_bar' => baz']];
~~~