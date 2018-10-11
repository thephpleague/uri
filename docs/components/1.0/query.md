---
layout: default
title: The Query component
redirect_from:
    - /5.0/components/query/
---

The Query
=======

The library provides a `Query` class to ease query string creation and manipulation. This URI component object exposes the [package common API](/components/1.0/api/), but also provide specific methods to work with the URI query component.

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">When a modification fails an <code>League\Uri\Components\Exception</code> exception is thrown.</p>

<p class="message-warning">
Since version <code>1.5.0</code>:<br>
<code>Query::parse</code> is deprecated and replaced by <code>QueryParser::parse</code><br>
<code>Query::extract</code> is deprecated and replaced by <code>QueryParser::extract</code><br>
<code>Query::build</code> is deprecated and replaced by <code>QueryBuilder::build</code><br>
Please refers to the <a href="components/1.0/parsers">Query Parsers</a> documentation page for more informations.
</p>

## Basic usage

~~~php
<?php
public Query::__construct(?string $content = null, string $separator = '&'): void
public Query::append(string $query): self
public Query::getSeparator(string $separator): self
public Query::ksort(mixed $sort): self
public Query::merge(string $query): self
public Query::withSeparator(string $separator): self
~~~

### Query::__construct

~~~php
<?php
public Query::__construct(?string $content = null, string $separator = '&'): void
~~~

<p class="message-info">The optional <code>$separator</code> argument was added in <code>version 1.3.0</code></p>
<p class="message-notice">submitted string is normalized to be <code>RFC3986</code> compliant.</p>
<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Components\Exception</code> exception is thrown.</p>

The `League\Uri\Components\Exception` extends PHP's SPL `InvalidArgumentException`.


### Query::getSeparator and Query::withSeparator

<p class="message-info"><code>Query::getSeparator</code> and <code>Query::withSeparator</code> are available since version <code>1.3.0</code></p>

~~~php
<?php
public Query::getSeparator(string $separator): self
public Query::withSeparator(void): string
~~~

`Query::getSeparator` returns the current separator attached to the `Query` object` while `Query::withSeparator` returns a new `Query` object with an alternate string separator.

`Query::withSeparator` expects a single argument which is a string separator. If the separator is equal to `=` an exception will be thrown.

~~~php
<?php

use League\Uri\Components\Query;

$query    = new Query('foo=bar&baz=toto');
$newQuery = $query->withSeparator('|');
$newQuery->__toString(); //return foo=bar|baz=toto
~~~

### Query::merge

`Query::merge` returns a new `Query` object with its data merged.

~~~php
<?php

public Query::merge(string $query): Query
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

public Query::append(string $query): Query
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

## Query as a PHP data transport layer

~~~php
<?php

public static Query::createFromParams(array $params, string $separator = '&'): self
public Query::getParams(): array
public Query::getParam(string $name, $default = null): mixed
public Query::withoutNumericIndices(): self
public Query::withoutParams(string[] $offsets): self
~~~

### Query::createFromParams

<p class="message-info"><code>Query::createFromParams</code> is available since version <code>1.3.0</code></p>

This named constructor takes any iterable construct and tries to recreate a `Query` object using internally `http_build_query`. This method takes 2 arguments:

- `$params` : a iterable containing properties to transform into a query string;
- `$separator`: the separator to be used when creating the query string representation;

~~~php
<?php

use League\Uri\Components\Query;

$query = Query::createFromParams(['foo' => 'bar', 'filter' => ['status' => 'on', 'order' => 'desc']], '&amp;');
echo $query->getContent(Query::NO_ENCODING); //return 'foo=bar&amp;filter[status]=on&amp;filter[order]=desc';
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


### Query::withoutParams

<p class="message-info"><code>Query::withoutParams</code> is available since version <code>1.3.0</code></p>

If you want to remove PHP's variable from the query string you can use the `Query::withoutParams` method as shown below

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('foo[]=bar&foo[]=y+olo&z=');
$new_query = $query->withoutParams(['foo']);
$new_query->getParam('foo'); //return null
echo $new_query->getContent(Query::NO_ENCODING); //return 'z='
~~~

### Query::withoutNumericIndices

<p class="message-info"><code>Query::withoutNumericIndices</code> is available since version <code>1.3.0</code></p>

If your query string is created with `http_build_query` or the `Query::createFromParams` named constructor chances are that numeric indices have been added by the method. The `Query::withoutNumericIndices` removes any numeric index found in the query string as shown below:

~~~php
<?php

use League\Uri\Components\Query;

$query = Query::createFromParms('foo[]=bar&foo[]=baz');
echo $query->getContent(Query::NO_ENCODING); //return 'foo[0]=bar&foo[1]=baz'
$new_query = $query->withoutNumericIndices();
echo $new_query->getContent(Query::NO_ENCODING); //return 'foo[]=bar&foo[]=baz'
//of note both objects returns the same PHP's variables but differs regarding the pairs
$query->getParams(); //return ['foo' => ['bar', 'baz']]
$new_query->getParams(); //return ['foo' => ['bar', 'baz']]
~~~

## Query as a collection of key/value pairs

~~~php
<?php

public static Query::createFromPairs(iterable $pairs[, string $separator = '&']): self
public Query::getPairs(): array
public Query::getPair(string $offset, $default = null): mixed
public Query::hasPair(string $offset): bool
public Query::keys([mixed $value = null]): array
public Query::withoutEmptyPairs(): self
public Query::withoutPairs(array $offsets): self
~~~

This class mainly represents the query string as a collection of key/value pairs.

### Query::createFromPairs

Returns a new `Query` object from an `array` or a `Traversable` object.

* `$pairs` : The submitted data must be an `array` or a `Traversable` key/value structure similar to the result of [Query::parse](#parsing-the-query-string-into-an-array).

* `$separator` : The query string separator used for string representation. By default equals to `&`;

<p class="message-info"><code>$separator</code> is availiabe since version <code>1.3.0</code>.</p>

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

### Query::withoutEmptyPairs

`Query::withoutEmptyPairs` returns a new `Query` object with deleted empty pairs. A pair is considered empty if its key equals the empty string and its value is `null`.

~~~php
<?php

use League\Uri\Components\Query;

$query    = new Query('&&=toto&&&&=&');
$newQuery = $query->withoutEmptyPairs();
echo $query; //displays '&&=toto&&&&=&'
echo $newQuery; //displays '=toto&='
~~~