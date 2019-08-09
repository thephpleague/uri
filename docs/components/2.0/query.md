---
layout: default
title: The Query component
---

The Query
=======

The library provides a `League\Uri\Components\Query` class to ease query string creation and manipulation. This URI component object exposes the [package common API](/components/2.0/api/), but also provide specific methods to work with the URI query component.

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Exceptions\SyntaxError</code> exception is thrown.</p>

## Standard instantiation

<p class="message-warning">The default constructor is private and can not be accessed to instantiate a new object.</p>

<p class="message-info">The <code>$query</code> paramater supports parameter widening. Apart from strings, scalar values and objects implementing the <code>__toString</code> method can be used.</p>

### Using a RFC3986 query string

~~~php
<?php

use League\Uri\Components\Query;

$query = Query::createFromRFC3986('foo=bar&bar=baz%20bar', '&');
$query->params('bar'); // returns 'baz bar'
~~~

This named constructor is capable to instantiate a query string encoded using [RFC3986](https://tools.ietf.org/html/rfc3986#section-3.4) query component rules.

### Using a RFC1738 query string

~~~php
$query = Query::createFromRFC1738('foo=bar&bar=baz+bar', '&');
$query->params('bar'); // returns 'baz bar'
~~~

This named constructor is capable to instantiate a query string encoded using using [application/x-www-form-urlencoded](https://url.spec.whatwg.org/#urlencoded-parsing) rules;

In addition to the string representation methods from the [package common API](/components/2.0/api/), the following methods are available.

### Query separator

The query separator is essential to query manipulation. The `Query` object provides two (2) simple methods to interact with its separator:

~~~php
public Query::getSeparator(string $separator): self
public Query::withSeparator(): string
~~~

`Query::getSeparator` returns the current separator attached to the `Query` object while `Query::withSeparator` returns a new `Query` object with an alternate string separator.

`Query::withSeparator` expects a single argument which is a string separator. If the separator is equal to `=` an exception will be thrown.

~~~php
$query    = Query::createFromRFC3986('foo=bar&baz=toto');
$newQuery = $query->withSeparator('|');
$newQuery->__toString(); //return foo=bar|baz=toto
~~~

## Component representations

### RFC3986 representation

The `Query` object can return the query encoded using the [RFC3986](https://tools.ietf.org/html/rfc3986#section-3.4) query component rules

~~~php
$query = Query::createFromRFC1738('foo=bar&bar=baz+bar', '&');
$query->toRFC3986();  //returns 'foo=bar&bar=baz%20bar'
$query->getContent(); //returns 'foo=bar&bar=baz%20bar'
~~~

If the query is undefined, this method returns `null`.

<p class="message-info"><code>Query::getContent()</code> is a alias of <code>Query::toRFC3986()</code></p>

### RFC1738 representation

The `Query` object can returns the query encoded using the  [application/x-www-form-urlencoded](https://url.spec.whatwg.org/#urlencoded-parsing) query component rules

~~~php
$query = Query::createFromRFC3986('foo=bar&bar=baz%20bar', '&');
$query->toRFC1738(); // returns 'foo=bar&bar=baz+bar'
$query->jsonSerialize(); //returns 'foo=bar&bar=baz+bar'
~~~

If the query is undefined, this method returns `null`.

<p class="message-info"><code>Query::jsonSerialize()</code> is a alias of <code>Query::toRFC1738()</code> to improve interoperability with JavaScript.</p>

## Modifying the query

### Query::merge

`Query::merge` returns a new `Query` object with its data merged.

~~~php
<?php

public Query::merge($query): Query
~~~

This method expects a single argument which is a string

~~~php
$query    = Query::createFromRFC3986('foo=bar&baz=toto');
$newQuery = $query->merge('foo=jane&r=stone');
$newQuery->__toString(); //return foo=jane&baz=toto&r=stone
// the 'foo' parameter was updated
// the 'r' parameter was added
~~~

<p class="message-info">Values equal to <code>null</code> or the empty string are merge differently.</p>

~~~php
$query    = Query::createFromRFC3986('foo=bar&baz=toto');
$newQuery = $query->merge('baz=&r');
$newQuery->__toString(); //return foo=bar&baz=&r
// the 'r' parameter was added without any value
// the 'baz' parameter was updated to an empty string and its = sign remains
~~~

### Query::append

`Query::append` returns a new `Query` object with its data append to it.

~~~php
public Query::append($query): Query
~~~

This method expects a single argument which is a string, a scalar or an object with the `__toString` method.

~~~php
$query    = Query::createFromRFC3986('foo=bar&john=doe');
$newQuery = $query->append('foo=baz');
$newQuery->__toString(); //return foo=jane&foo=baz&john=doe
// a new foo parameter is added
~~~

### Query::sort

`Query::sort` returns a `Query` object with its pairs sorted according to its keys. Sorting is done so
that parsing stayed unchanged before and after processing the query.

~~~php
$query    = Query::createFromRFC3986('foo=bar&baz=toto&foo=toto');
$newQuery = $query->sort();
$newQuery->__toString(); //return foo=bar&foo=toto&baz=toto
~~~

## Using the Query as a PHP data transport layer

~~~php
public static Query::createFromParams($params, string $separator = '&'): self
public Query::params(?string $name = null): mixed
public Query::withoutNumericIndices(): self
public Query::withoutParam(...string $offsets): self
~~~

### Using PHP data structure to instantiate a new Query object

Historically, the query string has been used as a data transport layer of PHP variables. The `createFromParams` uses
PHP own data structure to generate a query string *à la* `http_build_query`.

~~~php
parse_str('foo=bar&bar=baz+bar', $params);

$query = Query::createFromParams($params, '|');
echo $query->getContent(); // returns 'foo=bar|bar=baz%20bar'
~~~

<p class="message-info">The <code>$params</code> input can be any argument type supported by <code>http_build_query</code> which means that it can be an <code>iterable</code> or 
an object with public properties.</p>

<p class="message-notice">If you want a better parsing you can use the <a href="/components/2.0/query-parser-builder/">QueryString</a> class.</p>

### Query::params

If you already have an instantiated `Query` object you can return all the query string deserialized arguments using the `Query::params` method:

~~~php
$query_string = 'foo.bar=bar&foo_bar=baz';
parse_str($query_string, $out);
var_export($out);
// $out = ["foo_bar" => 'baz'];

$arr = Query::createFromRFC3986($query_string))->params();
// $arr = ['foo.bar' => 'bar', 'foo_bar' => baz']];
~~~


If you are only interested in a given argument you can access it directly by supplyling the argument name as show below:

~~~php
$query = Query::createFromRFC3986('foo[]=bar&foo[]=y+olo&z=');
$query->params('foo');   //return ['bar', 'y+olo']
$query->params('gweta'); //return null
~~~

The method returns the value of a specific argument. If the argument does not exist it will return `null`.


### Query::withoutParam

If you want to remove PHP's variable from the query string you can use the `Query::withoutParams` method as shown below

~~~php
$query = Query::createFromRFC3986('foo[]=bar&foo[]=y+olo&z=');
$new_query = $query->withoutParam('foo');
$new_query->params('foo'); //return null
echo $new_query->getContent(); //return 'z='
~~~

<p class="message-info">This method takes a variadic arguments representing the keys to be removed.</p>

### Query::withoutNumericIndices

If your query string is created with `http_build_query` or the `Query::createFromParams` named constructor chances are that numeric indices have been added by the method.

The `Query::withoutNumericIndices` removes any numeric index found in the query string as shown below:

~~~php
$query = Query::createFromParms('foo[]=bar&foo[]=baz');
echo $query->getContent(); //return 'foo[0]=bar&foo[1]=baz'
$new_query = $query->withoutNumericIndices();
echo $new_query->getContent(Query::NO_ENCODING); //return 'foo[]=bar&foo[]=baz'
//of note both objects returns the same PHP's variables but differs regarding the pairs
$query->params(); //return ['foo' => ['bar', 'baz']]
$new_query->params(); //return ['foo' => ['bar', 'baz']]
~~~

## Using the Query as a collection of query pairs

This class mainly represents the query string as a collection of key/value pairs.

~~~php
public static Query::createFromPairs(iterable $pairs, string $separator = '&'): self
public Query::count(): int
public Query::getIterator(): iterable
public Query::pairs(): iterable
public Query::has(string $key): bool
public Query::get(string $key): ?string
public Query::getAll(string $key): array
public Query::withPair(string $key, $value): QueryInterface
public Query::withoutDuplicates(): self
public Query::withoutEmptyPairs(): self
public Query::withoutPair(string ...$keys): QueryInterface
public Query::appendTo(string $key, $value): QueryInterface
~~~

### Query::createFromPairs

~~~php
$pairs = QueryString::parse('foo=bar&bar=baz%20bar', '&', PHP_QUERY_RFC3986);
$query = Query::createFromPairs($pairs, '|');

echo $query->getContent(); // returns 'foo=bar|bar=baz%20bar'
~~~

The `$pairs` input must an iterable which exposes the same structure as `QueryString::parse` return type structure.

Returns a new `Query` object from an `array` or a `Traversable` object.

* `$pairs` : The submitted data must be an `array` or a `Traversable` key/value structure similar to the result of [Query::parse](#parsing-the-query-string-into-an-array).

* `$separator` : The query string separator used for string representation. By default equals to `&`;

#### Examples

~~~php
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
$query = new Query::createFromRFC1738('foo=bar&p=y+olo&z=');
count($query); //return 3
foreach ($query as $pair) {
    //first round 
    // $pair = ['foo', 'bar']
    //second round
    // $pair = ['p', 'y olo']
}
~~~

<p class="message-info">When looping the key and the value are decoded.</p>

### Query::pairs

The `Query::pairs` method returns an iterator which enable iterating over each pair where the offset represent the pair name
 while the value represent the pair value.

~~~php
$query = Query::createFromRFC3986('foo=bar&foo=BAZ&p=y+olo&z=');
foreach ($query->pairs() as $name => $value) {
    //first round 
    // $name = 'foo' and $value = 'bar'
    //second round
    // $name = 'foo' and $value = 'BAZ'
}
~~~

<p class="message-info">The returned iterable contains decoded data.</p>

### Query::has

Because a pair value can be `null` the `Query::has` method is used to remove the possible `Query::get` result ambiguity.

~~~php
$query = Query::createFromRFC3986('foo=bar&p&z=');
$query->getPair('foo');   //return 'bar'
$query->getPair('p');     //return null
$query->getPair('gweta'); //return null

$query->has('gweta'); //return false
$query->has('p');     //return true
~~~

### Query::get

If you are only interested in a given pair you can access it directly using the `Query::get` method as show below:

~~~php
$query = Query::createFromRFC3986('foo=bar&foo=BAZ&p=y+olo&z=');
$query->get('foo');   //return 'bar'
$query->get('gweta');  //return null
~~~

The method returns the first value of a specific pair key as explained in the WHATWG documentation. If the key does not exist `null` will be returned.

<p class="message-info">The returned data are fully decoded.</p>

### Query::getAll

This method will return all the value associated with its submitted `$name`.

~~~php
$query = Query::createFromRFC3986('foo=bar&foo=BAZ&p=y+olo&z=');
$query->getAll('foo');   //return ['bar', 'BAZ']
$query->getAll('gweta');  //return null
~~~

### Query::withoutPair

`Query::withoutPair` returns a new `Query` object with deleted pairs according to their keys.

This method expects an array containing a list of keys to remove as its single argument.

~~~php
$query    = Query::createFromRFC3986('foo=bar&p=y+olo&z=');
$newQuery = $query->withoutPair('foo', 'p');
echo $newQuery; //displays 'z='
~~~

### Query::withoutEmptyPairs

`Query::withoutEmptyPairs` returns a new `Query` object with deleted empty pairs. A pair is considered empty if its key equals the empty string and its value is `null`.

~~~php
$query    = Query::createFromRFC3986('&&=toto&&&&=&');
$newQuery = $query->withoutEmptyPairs();
echo $query; //displays '&&=toto&&&&=&'
echo $newQuery; //displays '=toto&='
~~~
