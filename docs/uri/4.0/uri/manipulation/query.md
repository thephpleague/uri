---
layout: default
title: URI Modifiers which affect the URI Query component
redirect_from:
    - /4.0/uri/manipulation/query/
---

# Query modifiers

Here's the documentation for the included URI modifiers which are modifying the URI query component.

## Sorting the query keys

### Description

~~~php
<?php

public KsortQuery::__construct(mixed $sort = SORT_REGULAR)
~~~

Sorts the query according to its key values.

### Parameters

The `$sort` argument can be:

- one of PHP's sorting constant used by the [ksort function](http://php.net/ksort)

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\KsortQuery;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new KsortQuery(SORT_REGULAR);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?foo=bar%20baz&kingkong=toto#doc3"
~~~

- a user defined comparison function used by the [uksort function](http://php.net/uksort)

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\KsortQuery;

$sort = function ($value1, $value2) {
    return strcasecmp($value1, $value2);
};

$modifier = new KsortQuery($sort);

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?foo=bar%20baz&kingkong=toto#doc3"
~~~

### Methods

<p class="message-warning">The <code>withAlgorithm</code> method is deprecated since <code>version 4.1</code> and will be removed in the next major release</p>

The sorting algorithm can be change at any given time. By default if none is provided. The sorting is done using PHP's `ksort` sort flag parameters. But you can also provide a callable as sorting mechanism using the `withAlgorithm` method.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\KsortQuery;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new KsortQuery();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?foo=bar%20baz&kingkong=toto#doc3"
$altModifier = $modifier->withAlgorithm(function ($value1, $value2) {
    return strcasecmp($value1, $value2);
});
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://example.com/test.php?foo=bar%20baz&kingkong=toto#doc3"
~~~

## Merging query string

### Description

~~~php
<?php

public MergeQuery::__construct(string $query)
~~~

Merges a submitted query string to the URI object to be modified

### Example

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\MergeQuery;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new MergeQuery('kingkong=godzilla&toto');
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?kingkong=godzilla&foo=bar%20baz&&toto#doc3"
~~~

### Methods

<p class="message-warning">The <code>withQuery</code> method is deprecated since <code>version 4.1</code> and will be removed in the next major release</p>

At any given time you can create a new modifier with another query string to merge using the `withQuery` method.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\MergeQuery;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new MergeQuery('kingkong=godzilla&toto');
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?kingkong=godzilla&foo=bar%20baz&&toto#doc3"
$altModifier = $modifier->withQuery('foo=1');
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://example.com/test.php?kingkong=toto&foo=1#doc3"
~~~

## Removing query keys

### Description

~~~php
<?php

public RemoveQueryKeys::__construct(array $keys = [])
~~~

Removes query keys from the current URI path.

### Examples

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveQueryKeys;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new RemoveQueryKeys(["foo"]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?kingkong=toto#doc3"
~~~

### Methods

<p class="message-warning">The <code>withKeys</code> method is deprecated since <code>version 4.1</code> and will be removed in the next major release</p>

You can update the keys chosen by using the `withKeys` method

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveQueryKeys;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new RemoveQueryKeys(["foo"]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?kingkong=toto#doc3"
$altModifier = $modifier->withKeys(["kingkong"]);
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://example.com/test.php?foo=bar%20baz#doc3"
~~~

## Filtering query key/values

### Description

~~~php
<?php

public FilterQuery::__construct(callable $callable, int $flag = 0)
~~~

### Parameters

- The `$callable` argument is a `callable` used by PHP's `array_filter`
- The `$flag` argument is a `int` used by PHP's `array_filter`

<p class="message-notice">
For Backward compatibility with PHP5.5 which lacks these flags constant you can use the library constants instead:</p>

<table>
<thead>
<tr><th>League\Uri\Interfaces\Collection constants</th><th>PHP's 5.6+ constants</th></tr>
</thead>
<tbody>
<tr><td><code>Collection::FILTER_USE_KEY</code></td><td><code>ARRAY_FILTER_USE_KEY</code></td></tr>
<tr><td><code>Collection::FILTER_USE_BOTH</code></td><td><code>ARRAY_FILTER_USE_BOTH</code></td></tr>
<tr><td><code>Collection::FILTER_USE_VALUE</code></td><td><code>0</code></td></tr>
</tbody>
</table>

### Examples

Filter selected query keys and/or values from the current URI path to keep.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\FilterQuery;
use League\Uri\Interfaces\Collection;

$filter = function ($value) {
    return strpos($value, 'f');
};
$uriString = "http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3";
$uri = Http::createFromString($uriString);
$modifier = new FilterQuery($filter, Collection::FILTER_USE_KEY);
echo $newUri; //display "http://example.com/test.php?foo=bar%20baz#doc3"
~~~

### Methods

<p class="message-warning">The <code>withCallable</code> and <code>withFlag</code> methods are deprecated since <code>version 4.1</code> and will be removed in the next major release</p>

You can update the URI modifier using:

- `withCallable` method to alter the filtering function;
- `withFlag` method to alter the filtering flag;

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\FilterQuery;
use League\Uri\Interfaces\Collection;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new FilterQuery(function ($value) {
    return strpos($value, 'f');
}, Collection::FILTER_USE_KEY);
echo $newUri; //display "http://example.com/test.php?foo=bar%20baz#doc3"
$altModifier = $modifier->withCallable(function ($value) {
    return false !== strpos($value, 'o');
})->withFlag(Collection::FILTER_USE_VALUE');
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://example.com/test.php?kingkong=toto#doc3"
~~~
