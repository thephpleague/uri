---
layout: default
title: URI Modifiers which affect the URI Query component
---

# Query component modifiers

## Sorting the query keys

Sorts the query according to its key values.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\KsortQuery;

$uri = HttpUri::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new KsortQuery();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?foo=bar%20baz&kingkong=toto#doc3"
~~~

The sorting algorithm can be change at any given time. By default if none is provided. The sorting is done using PHP's `ksort` sort flag parameters. But you can also provide a callable as sorting mechanism using the `withAlgorithm` method.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\KsortQuery;

$uri = HttpUri::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
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

Merges a submitted query string to the URI object to be modified

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\MergeQuery;

$uri = HttpUri::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new MergeQuery('kingkong=godzilla&toto');
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?kingkong=godzilla&foo=bar%20baz&&toto#doc3"
~~~

At any given time you can create a new modifier with another query string to merge using the `withQuery` method.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\MergeQuery;

$uri = HttpUri::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new MergeQuery('kingkong=godzilla&toto');
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?kingkong=godzilla&foo=bar%20baz&&toto#doc3"
$altModifier = $modifier->withQuery('foo=1');
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://example.com/test.php?kingkong=toto&foo=1#doc3"
~~~


## Removing query keys

Removes query keys from the current URI path.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\RemoveQueryKeys;

$uri = HttpUri::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new RemoveQueryKeys(["foo"]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?kingkong=toto#doc3"
~~~

You can update the keys chosen by using the `withKeys` method

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\RemoveQueryKeys;

$uri = HttpUri::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new RemoveQueryKeys(["foo"]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?kingkong=toto#doc3"
$altModifier = $modifier->withKeys(["kingkong"]);
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://example.com/test.php?foo=bar%20baz#doc3"
~~~

## Filtering query key/values

Filter selected query keys and/or values from the current URI path to keep.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\FilterQuery;
use League\Uri\Interfaces\Collection;

$uri = HttpUri::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new FilterQuery(function ($value) {
    return strpos($value, 'f');
}, Collection::FILTER_USE_KEY);
echo $newUri; //display "http://example.com/test.php?foo=bar%20baz#doc3"
~~~

You can update the URI modifier using:

- `withCallable` method to alter the filtering function
- `withFlag` method to alter the filtering flag. depending on which parameter you want to use to filter the path you can use:
	- the `Collection::FILTER_USE_KEY` to filter against the query parameter name;
	- the `Collection::FILTER_USE_VALUE` to filter against the query parameter value;
	- the `Collection::FILTER_USE_BOTH` to filter against the query parameter value and name;

If no flag is used, by default the `Collection::FILTER_USE_VALUE` flag is used.
If you are using PHP 5.6+ you can directly use PHP's `array_filter` constant:

- `ARRAY_FILTER_USE_KEY` in place of `Collection::FILTER_USE_KEY`
- `ARRAY_FILTER_USE_BOTH` in place of `Collection::FILTER_USE_BOTH`

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\FilterQuery;
use League\Uri\Interfaces\Collection;

$uri = HttpUri::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
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
