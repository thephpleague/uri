---
layout: default
title: The Query component
---

The Query
=======

The library provides a `Query` class to ease query string creation and manipulation.

## Raw manipulations

The library does not rely on PHP’s `parse_str` and `http_build_query` functions. Instead, the `Query` object exposes 3 methods to parse, build and extract data from the query string.

### Parsing the query string

~~~php
<?php

public static Query::parse(string $query_string [, string $separator = '&']): array
~~~

This method parse the query string into an associative `array` of key/pairs value. The methods expects two arguments:

- The query string **required**;
- The query string separator **optional**, by default it is set to `&`;

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

public static Query::extract(string $query[, string $separator = '&']): array
~~~

The `Query::extract` method returns an `array` representation of the query string similar to PHP's `parse_str`  when used with its optional second argument. The main difference are:

- `Query::extract` accepts a second parameter which describe the query string separator
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
// $arr = ["foo.bar" => 'bar', 'foo_bar' => baz']];
~~~

## Query as a Collection of key/pair values

If you prefer using a value object, the query string can be manipulate by create a new instance of the `Query` object. This URI component object exposes the [package common API](/5.0/components/api/), but also provide specific methods to work with the URI query component.

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">When a modification fails an <code>InvalidArgumentException</code> exception is thrown.</p>

### Creating a new instance from a key/pairs array

~~~php
<?php

public static function Query::createFromPairs($pairs): Query
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

### Accessing the query parameters

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


The class also implements PHP’s Countable and IteratorAggregate interfaces. This means that you can count the number of pairs and use the foreach construct to iterate over them.

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
count($query); //return 4
foreach ($query as $key => $value) {
    //do something meaningful here
}
~~~

<p class="message-info">When looping the returned data are fully decoded.</p>

### Accessing the pairs keys

If you are interested in getting the label offsets you can do so using the `Query::keys` method.

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
$query->keys();        //return ['foo', 'p', 'z'];
$query->keys('bar');   //return ['foo'];
$query->keys('gweta'); //return [];
~~~

By default, the method returns all the keys names, but if you supply a value, only the keys whose value equals the value are returned.

<p class="message-info">The supplied argument is fully decoded to enable matching the corresponding keys.</p>


### Accessing a specific pair

If you are only interested in a given pair you can access it directly using the `Query::getValue` method as show below:

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
$query->getValue('foo');          //return 'bar'
$query->getValue('gweta');        //return null
$query->getValue('gweta', 'now'); //return 'now'
~~~

The method returns the value of a specific parameter name. If the offset does not exist it will return the value specified by the second argument which defaults to `null`.

## Manipulating the query pairs

### Sorting the query

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

### Merging query strings

`Query::merge` returns a new `Query` object with its data merged.

~~~php
<?php

public function Query::merge(?string $query): Query
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

<p class="message-info">This method is used by the URI modifier <code>MergeQuery</code></p>

### Removing pairs from the query

`Query::without` returns a new `Query` object with deleted pairs according to their keys.

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

### Filtering the query

`Query::filter` Returns a new `Query` object with filtered pairs.

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

You can filter the query with both the value and the key

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