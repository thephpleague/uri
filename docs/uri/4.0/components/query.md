---
layout: default
title: The Query Component
    - /4.0/components/query/
---

# The Query component

The library provides a `Query` class to ease complex query manipulation.

## Query creation

### Using the default constructor

~~~php
<?php

public function __contruct(string $query = null)
~~~

The constructor accepts:

- a valid string according to RFC3986 rules;
- the `null` value;

#### Example

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=yolo&z=');
echo $query->getContent(); //display 'foo=bar&p=yolo&z'
echo $query; //display 'foo=bar&p=yolo&z'
echo $query->getUriComponent(); //display '?foo=bar&p=yolo&z'
~~~

<p class="message-info">When using the default constructor do not prepend your query delimiter to the string as it will be considered as part of the first parameter name.</p>

<p class="message-warning">If the submitted value is not a valid query an <code>InvalidArgumentException</code> will be thrown.</p>

### Using a League Uri object

~~~php
<?php

use League\Uri\Ws as WsUri;

$uri = WsUri::createFromString('wss://uri.thephpleague.com/path/to/here?foo=bar');
$query = $uri->query; //$query is a League\Uri\Components\Query object;
~~~

### Using a named constructor

<p class="message-warning">Since <code>version 4.2</code> <code>createFromPairs</code> replaces <code>createFromArray</code>. <code>createFromArray</code> is deprecated and will be removed in the next major release</p>

Returns a new `Query` object from an `array` or a `Traversable` object.

~~~php
<?php

public static function Query::createFromPairs(array $pairs): Query
~~~

* `$pairs` : The submitted data must be an `array` or a `Traversable` key/value structure similar to the result of [QueryParser::parse](/services/parser-query/#parsing-the-query-string-into-an-array).

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

The component representation, comparison and manipulation is done using the package [UriPart](/uri/4.0/components/overview/#uri-part-interface) and the [Component](/uri/4.0/components/overview/#uri-component-interface) interfaces methods.

## Accessing query pairs

<p class="message-warning">Since <code>version 4.2</code> <code>getPairs</code> replaces <code>toArray</code>. <code>toArray</code> is deprecated and will be removed in the next major release</p>

~~~php
<?php

public function Query::getIterator(): ArrayIterator
public function Query::count(): int
public function Query::getPairs(): array
public function Query::keys(mixed $value = null): array
public function Query::hasKey(string $offset): bool
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

<p class="message-warning">Since <code>version 4.2</code> <code>getPairs</code> replaces <code>toArray</code>. <code>toArray</code> is deprecated and will be removed in the next major release</p>

A query can be represented as an array of its internal key/value pairs. Through the use of the `Query::getPairs` method the class returns the object's array representation. This method uses `QueryParser::parse` to create the array.

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

### Query::hasKey

Tells whether the submitted key exists in the current `Query` object.

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo=bar&p=y+olo&z=');
$query->hasKey('p');    //return true
$query->hasKey('john'); //return false
~~~

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

public function Query::merge(mixed $query): Query
~~~

This method expects a single argument which can be:

A string or a stringable object:

~~~php
<?php

use League\Uri\Components\Query;

$query    = new Query('foo=bar&baz=toto');
$newQuery = $query->merge('foo=jane&r=stone');
$newQuery->__toString(); //return foo=jane&baz=toto&r=stone
// the 'foo' parameter was updated
// the 'r' parameter was added
~~~

Another `Query` object

~~~php
<?php

use League\Uri\Components\Query;

$query    = Query::createFromPairs(['foo' => 'bar', 'baz' => 'toto']);
$newQuery = $query->merge(new Query('foo=jane&r=stone'));
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

public function Query::without(array $keys): Query
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

public function Query::filter(callable $filter, $flag = Query::FILTER_USE_VALUE): Query
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

$newQuery = $query->filter($filter, Query::FILTER_USE_VALUE);
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

$newQuery = $query->filter($filter, Query::FILTER_USE_KEY);
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

$newQuery = $query->filter($filter, Query::FILTER_USE_BOTH);
echo $newQuery; //displays 'toto=foo'
~~~

By specifying the second argument flag you can change how filtering is done:

- use `Query::FILTER_USE_VALUE` to filter by pairs value;
- use `Query::FILTER_USE_KEY` to filter by pairs key;
- use `Query::FILTER_USE_BOTH` to filter by pairs value and key;

By default, if no flag is specified the method will filter the query using the `Query::FILTER_USE_VALUE` flag.

<p class="message-info">If you are using PHP 5.6+ you can substitute these constants with PHP's <code>array_filter</code> flags constants <code>ARRAY_FILTER_USE_KEY</code> and <code>ARRAY_FILTER_USE_BOTH</code></p>

<p class="message-notice">This method is used by the URI modifier <code>FilterQuery</code></p>
