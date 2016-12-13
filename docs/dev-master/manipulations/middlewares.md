---
layout: default
title: URI middlewares
---

URI middlewares
=======

## Definition

An URI middleware is a function or a class which eases modifying an URI. They encapsulate the logic to modify your submitted URI and reduce boilerplate codes.

For instance here's how you would update the query string from a given URI object:

~~~php
<?php

use League\Uri\Components\Query;
use Slim\Http\Uri as SlimUri;

$uri = SlimUri::createFromString("http://www.example.com?foo=toto#~typo");
$uriQuery = new Query($uri->getQuery());
$updateQuery = $uriQuery->merge("foo=bar&taz=");
$newUri = $uri->withQuery($updateQuery->__toString());
echo $newUri; // display http://www.example.com?foo=bar&taz#~typo
~~~

Using an URI middleware the code becomes

~~~php
<?php

use League\Uri\Modifier\MergeQuery;
use Slim\Http\Uri as SlimUri;

$modifier = new MergeQuery("foo=bar&taz=");
$uri = SlimUri::createFromString("http://www.example.com?foo=toto#~typo");
$newQuery = $modifier($uri);
echo $newUri; // display http://www.example.com?foo=bar&taz=#~typo
~~~

Technically, an URI middleware:

- is a callable. If the URI middleware is a class it must implement PHP’s `__invoke` method.
- expects its single argument to be an URI object which implements either:

    - `Psr\Http\Message\UriInteface`;
    - `League\Uri\Interfaces\Uri`;

- must return a instance of the submitted object.
- is transparent when dealing with error and exceptions. It must not alter of silence them apart from validating their own parameters.

Here's a the URI middleware signature

~~~php
<?php

function(Psr\Http\Message\UriInteface $uri): Psr\Http\Message\UriInteface
//or
function(League\Uri\Interfaces\Uri $uri): League\Uri\Interfaces\Uri
~~~

## Complete URI Middlewares

### Normalize a URI

To help wil URI objects comparison, the  <code>League\Uri\Modifiers\Normalize</code> URI modifier is introduce to normalize URI according to the following rules:

- The host component is converted into their ASCII representation;
- The path component is normalized by removing dot segments as per RFC3986;
- The query component is sorted according to its key offset;
- The scheme component is lowercased;

If you normalized two URI objects it become easier to compare them to determine if they are referring to the same resource:

~~~php
<?php

use League\Uri\Modifiers\Normalize;
use League\Uri\Schemes\Http;

$uri = Http::createFromString("http://스타벅스코리아.com/to/the/sky/");
$altUri = Http::createFromString("http://xn--oy2b35ckwhba574atvuzkc.com/path/../to/the/./sky/");
$modifier = new Normalize();

$newUri    = $modifier->__invoke($uri);
$newAltUri = $modifier->__invoke($altUri);

var_dump($newUri->__toString() === $newAltUri->__toString()); //return true
~~~

### Resolving a relative URI

The `Resolve` URI Modifier provides the mean for resolving an URI as a browser would for a relative URI. When performing URI resolution the returned URI is normalized according to RFC3986 rules. The uri to resolved must be another Uri object.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\Resolve;

$baseUri     = Http::createFromString("http://www.example.com/path/to/the/sky/");
$relativeUri = Http::createFromString("./p#~toto");
$modifier    = new Resolve($baseUri);
$newUri = $modifier->__invoke($relativeUri);
echo $newUri; //displays "http://www.example.com/path/to/the/sky/p#~toto"
~~~

### Relativize an URI

The `Relativize` URI Modifier provides the mean to construct a relative URI that when resolved against the same URI yields the same given URI. This modifier does the inverse of the Resolve modifier. The uri to relativize must be another Uri object.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\Relativize;
use League\Uri\Modifiers\Resolve;

$baseUri = Http::createFromString('http://www.example.com');
$relativizer = new Relativize($baseUri);
$resolver = new Resolve($baseUri);
$uri = Http::createFromString('http://www.example.com/?foo=toto#~typo');
$relativeUri = $relativizer($uri);
echo $relativeUri; // display "/?foo=toto#~typo
echo $resolver($relativeUri); // display 'http://www.example.com/?foo=toto#~typo'
~~~

<p class="message-notice">To be sure that both operations yield the expected results both URI must be normalized.</p>

### Applying multiple modifiers to a single URI

Since all modifiers returns a URI object instance it is possible to chain them together. To ease this chaining the package comes bundle with the `League\Uri\Modifiers\Pipeline` class. The class uses the pipeline pattern to modify the URI by passing the results from one modifier to the next one.

The `League\Uri\Modifiers\Pipeline` uses two methods:

- `Pipeline::pipe` to attach a URI modifier following the *First In First Out* rule.
- `Pipeline::process` to apply sequencially each attached URI modifier to the submitted URI object.

<p class="message-notice">The <code>Pipeline::process</code> is an alias of <code>Pipeline::__invoke</code>.</p>

~~~php
<?php

use League\Uri\Modifiers\HostToAscii;
use League\Uri\Modifiers\KsortQuery;
use League\Uri\Modifiers\Pipeline;
use League\Uri\Modifiers\RemoveDotSegments;
use League\Uri\Schemes\Http;

$origUri = Http::createFromString("http://스타벅스코리아.com/to/the/sky/");
$origUri2 = Http::createFromString("http://xn--oy2b35ckwhba574atvuzkc.com/path/../to/the/./sky/");

$modifier = (new Pipeline())
	->pipe(new RemoveDotSegment())
	->pipe(new HostToAscii())
	->pipe(new KsortQuery());

$origUri1Alt = $modifier->process($origUri1);
$origUri2Alt = $modifier->process($origUri2);

echo $origUri1Alt; //display http://xn--oy2b35ckwhba574atvuzkc.com/to/the/sky/
echo $origUri2Alt; //display http://xn--oy2b35ckwhba574atvuzkc.com/to/the/sky/
~~~

<p class="message-notice">The <code>League\Uri\Modifiers\Pipeline</code> is a URI modifier as well which can lead to advance modifications from you URI in a sane an normalized way.</p>

<p class="message-info">This class is heavily influenced by the <a href="http://pipeline.thephpleague.com">League\Pipeline</a> package.</p>


## Query Middlewares:

All middlewares normalize the component

### Sorting the query keys

Sorts the query according to its key values.

#### Using PHP sorting constant

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\KsortQuery;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new KsortQuery(SORT_REGULAR);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?foo=bar%20baz&kingkong=toto#doc3"
~~~

#### Using a user defined comparison function used by the [uksort function](http://php.net/uksort)

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

### Merging query string

Merges a submitted query string to the URI object to be modified

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\MergeQuery;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new MergeQuery('kingkong=godzilla&toto');
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?kingkong=godzilla&foo=bar%20baz&&toto#doc3"
~~~

### Removing query keys

Removes query keys from the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveQueryKeys;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new RemoveQueryKeys(["foo"]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/test.php?kingkong=toto#doc3"
~~~

### Filtering query key/values

Filter selected query keys and/or values from the current query to keep.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\FilterQuery;

$filter = function ($value) {
    return strpos($value, 'f');
};
$uriString = "http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3";
$uri = Http::createFromString($uriString);
$modifier = new FilterQuery($filter, ARRAY_FILTER_USE_KEY);
echo $newUri; //display "http://example.com/test.php?foo=bar%20baz#doc3"
~~~

## Path Middlewares

All middlewares normalize the URI path component

### Removing dot segments

Removes dot segments according to RFC3986:

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveDotSegments;

$uri = Http::createFromString("http://www.example.com/path/../to/the/./sky/");
$modifier = new RemoveDotSegments();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/to/the/sky/"
~~~

### Removing empty segments

Removes adjacent separators with empty segment.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveEmptySegments;

$uri = Http::createFromString("http://www.example.com/path//to/the//sky/");
$modifier = new RemoveEmptySegments();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky/"
~~~

### Removing trailing slash

Removes the path trailing slash if present

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveTrailingSlash;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveTrailingSlash();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky"
~~~

### Adding trailing slash

Adds the path trailing slash if not present

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AddTrailingSlash;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new AddTrailingSlash();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky"
~~~

### Removing leading slash

Removes the path leading slash if present.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveLeadingSlash;

$uri = Http::createFromString("/path/to/the/sky/");
$modifier = new RemoveLeadingSlash();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "path/to/the/sky"
~~~

### Adding leading slash

Adds the path leading slash if not present.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AddLeadingSlash;

$uri = Http::createFromString("path/to/the/sky/");
$modifier = new AddLeadingSlash();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "/path/to/the/sky"
~~~

### Appending segments

Appends a segment or a path to the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AppendSegment;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new AppendSegment("and/above");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky/and/above"
~~~

### Prepending segments

Prepends a segment or a path to the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\PrependSegment;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new PrependSegment("and/above");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/and/above/path/to/the/sky/and/above"
~~~

### Replacing segments

Replaces a segment from the current URI path with a new segment or path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\ReplaceSegment;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceSegment(3, "sea");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sea"
~~~

### Updating path extension

Adds, update and or remove the path extension from the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\Extension;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky");
$modifier = new Extension("csv");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/and/above/path/to/the/sky.csv"
~~~

### Removing selected segments

Removes selected segments from the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveSegments;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveSegments([1,3]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/the/and/above"
~~~

### Filtering selected segments

Filter selected segments from the current URI path to keep.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\FilterSegments;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new FilterSegments(function ($value) {
    return $value > 0 && $value < 2;
}, ARRAY_FILTER_USE_KEY);
echo $newUri; //display "http://www.example.com/to/"
~~~

### Update Data URI parameters

Update Data URI parameters

~~~php
<?php

use League\Uri\Schemes\Data as DataUri;
use League\Uri\Modifiers\DataUriParameters;

$uriString = "data:text/plain;charset=US-ASCII,Hello%20World!";
$uri = DataUri::createFromString($uriString);
$modifier = new DataUriParameters("charset=utf-8");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "data:text/plain;charset=utf-8,Hello%20World!"
~~~

### Transcoding Data URI from ASCII to Binary

Transcoding a data URI path from text to its base64 encoded version

~~~php
<?php

use League\Uri\Schemes\Data as DataUri;
use League\Uri\Modifiers\DataUriToBinary;

$uriString = "data:text/plain;charset=US-ASCII,Hello%20World!";
$uri = DataUri::createFromString($uriString);
$modifier = new DataUriToBinary();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "data:text/plain;charset=US-ASCII;base64,SGVsbG8gV29ybGQh"
~~~

### Transcoding Data URI from Binary to ascii

Transcoding a data URI path from text to its base64 encoded version

~~~php
<?php

use League\Uri\Schemes\Data as DataUri;
use League\Uri\Modifiers\DataUriToAscii;

$uriString = "data:text/plain;charset=US-ASCII;base64,SGVsbG8gV29ybGQh";
$uri = DataUri::createFromString($uriString);
$modifier = new DataUriToAscii();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "data:text/plain;charset=US-ASCII,Hello%20World!"
~~~

## Host Middlewares

All middlewares normalize the component

### Transcoding the host to ascii

Transcodes the host into its ascii representation according to RFC3986:

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\HostToAscii;

$uri = Http::createFromString("http://스타벅스코리아.com/to/the/sky/");
$modifier = new HostToAscii();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://xn--oy2b35ckwhba574atvuzkc.com/to/the/sky/"
~~~

<p class="message-notice">This middleware will have no effect on a <code>League\Uri\Schemes\Http</code> class as this conversion is done by default.</p>

### Transcoding the host to its IDN form

Transcodes the host into its idn representation according to RFC3986:

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\HostToUnicode;

$uriString = "http://xn--oy2b35ckwhba574atvuzkc.com/to/the/./sky/";
$uri = Http::createFromString($uriString);
$modifier = new HostToUnicode();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://스타벅스코리아.com/to/the/sky/"
~~~

<p class="message-notice">This middleware will have no effect on a <code>League\Uri\Schemes\Http</code> class as this conversion is done by default.</p>

### Removing Zone Identifier

Removes the host zone identifier if present

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveZoneIdentifier;

$uriString = 'http://[fe80::1234%25eth0-1]/path/to/the/sky.php';
$uri = Http::createFromString($uriString);
$modifier = new RemoveZoneIdentifier();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display 'http://[fe80::1234]/path/to/the/sky.php'
~~~

### Appending labels

Appends a label or a host to the current URI host.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AppendLabel;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new AppendLabel("fr");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com.fr/path/to/the/sky/"
~~~

### Prepending labels

Prepends a label or a host to the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\PrependLabel;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new PrependLabel("shop");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://shop.www.example.com/path/to/the/sky/and/above"
~~~

### Replacing labels

Replaces a label from the current URI host with a new label or a host.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\ReplaceLabel;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceLabel(2, "admin.shop");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://admin.shop.example.com/path/to/the/sky"
~~~

### Removing selected labels

Removes selected labels from the current URI host. Labels are indicated using an array containing the labels offsets.

<p class="message-notice">The host is considered as a hierarchical component, labels are indexed from right to left according to host RFC</p>

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveLabels;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveLabels([2]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/path/the/sky/"
~~~

### Filtering selected labels

Filter the labels from the current URI host to keep.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\FilterLabels;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new FilterLabels(function ($value) {
    return $value > 0 && $value < 2;
}, ARRAY_FILTER_USE_KEY);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example/path/to/the/sky/"
~~~
