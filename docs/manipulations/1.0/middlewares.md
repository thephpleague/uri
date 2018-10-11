---
layout: default
title: URI middlewares
redirect_from:
    - /5.0/manipulations/middlewares/
---

URI middlewares
=======

## Definition

An URI middleware is a class which provides a convenient mechanism for filtering and manipulating an URI object. The only **hard** requirement is that a URI middleware **MUST** returns an URI instance of the same type that the one it received.

## Example

For instance here's how you would update the query string from a given URI object:

~~~php
<?php

use Slim\Http\Uri;

$base_uri = "http://www.example.com?fo.o=toto#~typo";
$query_to_merge = 'fo.o=bar&taz=';

$uri = Uri::createFromString($base_uri);
$source_query = $uri->getQuery();
parse_str($source_query, $params);
parse_str($query_to_merge, $new_params);
$new_query = http_build_query(
    array_merge($params, $new_params),
    '',
    '&',
    PHP_QUERY_RFC3986
);

$new_uri = $uri->withQuery($new_query);
echo $new_uri; // display http://www.example.com?fo_o=bar&taz=#~typo
~~~

Using the provided `League\Uri\Modifiers\MergeQuery` middleware the code becomes

~~~php
<?php

use League\Uri\Modifiers\MergeQuery;
use Slim\Http\Uri;

$base_uri = "http://www.example.com?fo.o=toto#~typo";
$query_to_merge = 'fo.o=bar&taz=';

$uri = Uri::createFromString($base_uri);
$modifier = new MergeQuery($query_to_merge);

$new_uri = $modifier->process($uri);
echo $new_uri;
// display http://www.example.com?fo.o=bar&taz=#~typo
// $new_uri is a SlimUri object
~~~

<p class="message-info">Since version <code>1.1.0</code> The above code can ben even more simplyfied</p>

~~~php
<?php

use League\Uri;
use Slim\Http\Uri as SlimUri;

$base_uri = "http://www.example.com?fo.o=toto#~typo";
$query_to_merge = 'fo.o=bar&taz=';

$uri = SlimUri::createFromString($base_uri);
$new_uri = Uri\merge_query($uri, $query_to_merge);
echo $new_uri;
// display http://www.example.com?fo.o=bar&taz=#~typo
// $new_uri is a SlimUri object
~~~

In addition to merging the query to the URI, `MergeQuery` has:

- enforced `RFC3986` encoding through out the modifications;
- not mangle your data during merging;
- returned an URI object of the same class as the one it received;


## URI Middleware Interface

~~~php
<?php

public UriMiddlewareInterface::process($uri);
~~~

The `UriMiddlewareInterface::process` :

- expects its single argument to be an URI object which implements either:

    - `Psr\Http\Message\UriInteface`;
    - `League\Uri\Interfaces\Uri`;

- must return a instance of the same type as the submitted object.
- is transparent when dealing with error and exceptions. It must not alter of silence them apart from validating their own parameters.

<p class="message-info">To reduce BC break, all implemented URI middlewares still support the <code>__invoke</code> method. The method is an alias of the <code>process</code> method.</p>

~~~php
<?php

use League\Uri\Modifiers\MergeQuery;
use Slim\Http\Uri as SlimUri;

$uri = SlimUri::createFromString("http://www.example.com?fo.o=toto#~typo");
$new_uri = (new MergeQuery('fo.o=bar&taz='))($uri);
echo $new_uri; // display http://www.example.com?fo.o=bar&taz=#~typo
               // $new_uri is a SlimUri object
~~~

Converting a callable into a Uri Middleware is easy with the `CallableAdapter` class. This class takes a callable as its unique argument and adapt its usage to the `UriMiddlewareInterface` interface.

~~~php
<?php

use League\Uri\Modifiers\CallableAdapter;
use Slim\Http\Uri as SlimUri;

$callable = function ($uri) {
    return $uri->withHost('thephpleague.com');
};

$uri = SlimUri::createFromString("http://www.example.com?fo.o=toto#~typo");
$new_uri = (new CallableAdapter($callable))->process($uri);
echo $new_uri; // display http://thephpleague.com?fo.o=toto#~typo
               // $new_uri is a SlimUri object
~~~

## Middlewares which manipulate several URI components

### Resolving a relative URI

The `Resolve` URI Middleware provides the mean for resolving an URI as a browser would for a relative URI. When performing URI resolution the returned URI is normalized according to RFC3986 rules. The uri to resolved must be another Uri object.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\Resolve;

$baseUri     = Http::createFromString("http://www.example.com/path/to/the/sky/");
$relativeUri = Http::createFromString("./p#~toto");
$modifier    = new Resolve($baseUri);
$newUri = $modifier->process($relativeUri);
echo $newUri; //displays "http://www.example.com/path/to/the/sky/p#~toto"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\resolve</code> is available</p>

~~~php
<?php

use League\Uri;

$baseUri     = Uri\Http::createFromString("http://www.example.com/path/to/the/sky/");
$relativeUri = Uri\Http::createFromString("./p#~toto");
$newUri = Uri\resolve($relativeUri, $baseUri);
echo $newUri; //displays "http://www.example.com/path/to/the/sky/p#~toto"
~~~

### Relativize an URI

The `Relativize` URI Middleware provides the mean to construct a relative URI that when resolved against the same URI yields the same given URI. This modifier does the inverse of the Resolve modifier. The uri to relativize must be another Uri object.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\Relativize;
use League\Uri\Modifiers\Resolve;

$baseUri = Http::createFromString('http://www.example.com');
$relativizer = new Relativize($baseUri);
$resolver = new Resolve($baseUri);
$uri = Http::createFromString('http://www.example.com/?foo=toto#~typo');
$relativeUri = $relativizer->process($uri);
echo $relativeUri; // display "/?foo=toto#~typo
echo $resolver->process($relativeUri); // display 'http://www.example.com/?foo=toto#~typo'
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\relativize</code> is available</p>

~~~php
<?php

use League\Uri;

$baseUri = Uri\create('http://www.example.com');
$uri = Uri\create('http://www.example.com/?foo=toto#~typo');

$relativeUri = Uri\relativize($uri, $baseUri);
echo $relativeUri; // display "/?foo=toto#~typo

$newUri = Uri\resolve($relativeUri, $baseUri);
echo $newUri; //displays "http://www.example.com/path/to/the/sky/p#~toto"
~~~

### URI comparison

To help with URI objects comparison, the  <code>League\Uri\Modifiers\Normalize</code> URI Middleware is introduce to normalize URI according to the following rules:

- The host component is converted into their ASCII representation;
- The path component is normalized by removing dot segments as per RFC3986;
- The query component is sorted according to its key offset;
- The scheme component is lowercased;
- Unreserved characters are decoded;

If you normalized two URI objects it become easier to compare them to determine if they are representing the same resource:

~~~php
<?php

use League\Uri\Modifiers\Normalize;
use League\Uri\Schemes\Http;

$uri = Http::createFromString("http://스타벅스코리아.com/to/the/sky/");
$altUri = Http::createFromString("http://xn--oy2b35ckwhba574atvuzkc.com/path/../to/the/./sky/");
$modifier = new Normalize();

$newUri    = $modifier->process($uri);
$newAltUri = $modifier->process($altUri);

var_dump($newUri->__toString() === $newAltUri->__toString()); //return true
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\normalize</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://스타벅스코리아.com/to/the/sky/");
$altUri = Uri\create("http://xn--oy2b35ckwhba574atvuzkc.com/path/../to/the/./sky/");

var_dump((string) Uri\normalize($uri) === (string) Uri\normalize($altUri)); //return true
~~~

<p class="message-notice">You should avoid using the Normalize URI middleware for anything else but URI comparison as some changes applied may introduce some data loss.</p>

### Applying multiple URI middleware to a single URI

Since all URI middleware returns a URI object instance it is possible to chain them together. To ease this chaining the package comes bundle with the `League\Uri\Modifiers\Pipeline` class. The class uses the pipeline pattern to modify the URI by passing the results from one modifier to the next one.

The `League\Uri\Modifiers\Pipeline` uses the `Pipeline::pipe` to attach a URI Middleware following the *First In First Out* rule.

~~~php
<?php

use League\Uri\Modifiers\HostToAscii;
use League\Uri\Modifiers\KsortQuery;
use League\Uri\Modifiers\Pipeline;
use League\Uri\Modifiers\RemoveDotSegments;
use League\Uri\Schemes\Http;
use Slim\Http\Uri;

$origUri = Uri::createFromString("http://스타벅스코리아.com/to/the/sky/");
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

<p class="message-notice">The <code>League\Uri\Modifiers\Pipeline</code> is a URI middleware as well which can lead to advance modifications from you URI in a sane an normalized way.</p>

<p class="message-info">This class is heavily influenced by the <a href="http://pipeline.thephpleague.com">League\Pipeline</a> package.</p>

## Query specific URI Middlewares

In addition to modifiying the URI query component, the middleware normalizes the query string to RFC3986

### Sorting the query keys

Sorts the query according to its key values.

#### Using PHP sorting constant

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\KsortQuery;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new KsortQuery(SORT_REGULAR);
$newUri = $modifier->process($uri);
echo $newUri; //display "http://example.com/test.php?foo=bar%20baz&kingkong=toto#doc3"
~~~

#### Using a user defined comparison function like the [uksort function](http://php.net/uksort)

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\KsortQuery;

$sort = function ($value1, $value2) {
    return strcasecmp($value1, $value2);
};

$modifier = new KsortQuery($sort);

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://example.com/test.php?foo=bar%20baz&kingkong=toto#doc3"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\sort_query</code> is available</p>

~~~php
<?php

use League\Uri;

$sort = function ($value1, $value2) {
    return strcasecmp($value1, $value2);
};

$uri = Uri\create("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$newUri = Uri\sort_query($uri, $sort);
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
$newUri = $modifier->process($uri);
echo $newUri; //display "http://example.com/test.php?kingkong=godzilla&foo=bar%20baz&toto#doc3"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\merge_query</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$newUri = Uri\merge_query($uri, 'kingkong=godzilla&toto');
echo $newUri; //display "http://example.com/test.php?kingkong=godzilla&foo=bar%20baz&toto#doc3"
~~~

### Append data to the query string

Append a submitted query string to the URI object to be modified.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AppendQuery;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new AppendQuery('kingkong=godzilla&toto');
$newUri = $modifier->process($uri);
echo $newUri; //display "http://example.com/test.php?kingkong=toto&kingkong=godzilla&foo=bar%20baz&toto#doc3"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\append_query</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$newUri = Uri\append_query($uri, 'kingkong=godzilla&toto');
echo $newUri; //display "http://example.com/test.php?kingkong=toto&kingkong=godzilla&foo=bar%20baz&toto#doc3"
~~~

### Removing query pairs

Removes query pairs from the current URI query string by providing the pairs key.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveQueryKeys;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$modifier = new RemoveQueryKeys(["foo"]);
$newUri = $modifier->process($uri);
echo $newUri; //display "http://example.com/test.php?kingkong=toto#doc3"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\remove_pairs</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$newUri = Uri\remove_pairs($uri, ['foo']);
echo $newUri; //display "http://example.com/test.php?kingkong=toto#doc3"
~~~

### Removing query params

<p class="message-info">Since version <code>1.3.0</code></p>

Removes query params from the current URI query string by providing the param name. The removal preserves mangled key params.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveQueryParams;

$uri = Http::createFromString("http://example.com/test.php?kingkong=toto&fo.o=bar&fo_o=bar");
$modifier = new RemoveQueryParams(["fo.o"]);
$newUri = $modifier->process($uri);
echo $newUri; //display "http://example.com/test.php?fo_o=bar"
~~~

The `Uri\remove_params` functions also exists.

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3");
$newUri = Uri\remove_pairs($uri, ['foo']);
echo $newUri; //display "http://example.com/test.php?kingkong=toto#doc3"
~~~

## Path specific URI Middlewares

In addition to modifiying the URI path component, the middleware normalizes the path encoding to RFC3986.

### Removing dot segments

Removes dot segments according to RFC3986:

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveDotSegments;

$uri = Http::createFromString("http://www.example.com/path/../to/the/./sky/");
$modifier = new RemoveDotSegments();
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/to/the/sky/"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\remove_dot_segments</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/../to/the/./sky/");
$newUri = Uri\remove_dot_segments($uri);
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
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky/"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\remove_empty_segments</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path//to/the//sky/");
$newUri = Uri\remove_empty_segments($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky/"
~~~

### Removing trailing slash

Removes the path trailing slash if present

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveTrailingSlash;

$uri = Http::createFromString("http://www.example.com/path/?foo=bar");
$modifier = new RemoveTrailingSlash();
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/path?foo=bar"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\remove_trailing_slash</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/?foo=bar");
$newUri = Uri\remove_trailing_slash($uri);
echo $newUri; //display "http://www.example.com/path?foo=bar"
~~~

### Adding trailing slash

Adds the path trailing slash if not present

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AddTrailingSlash;

$uri = Http::createFromString("http://www.example.com/sky#top");
$modifier = new AddTrailingSlash();
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/sky/#top"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\add_trailing_slash</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/sky#top");
$newUri = >Uri\add_trailing_slash($uri);
echo $newUri; //display "http://www.example.com/sky/#top"
~~~

### Removing leading slash

Removes the path leading slash if present.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveLeadingSlash;

$uri = Http::createFromString("/path/to/the/sky/");
$modifier = new RemoveLeadingSlash();
$newUri = $modifier->process($uri);
echo $newUri; //display "path/to/the/sky"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\remove_leading_slash</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Http::createFromString("/path/to/the/sky/");
$newUri = Uri\remove_leading_slash($uri);
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
$newUri = $modifier->process($uri);
echo $newUri; //display "/path/to/the/sky"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\add_leading_slash</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Http::createFromString("path/to/the/sky/");
$newUri = Uri\add_leading_slash($uri);
echo $newUri; //display "/path/to/the/sky"
~~~

### Updating path dirname

Adds, update and or remove the path dirname from the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\Dirname;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky");
$modifier = new Dirname("/road/to");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/road/to/sky"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\replace_dirname</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/to/the/sky");
$newUri = Uri\replace_dirname($uri, "/road/to");
echo $newUri; //display "http://www.example.com/road/to/sky"
~~~

### Updating path basename

Adds, update and or remove the path basename from the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\Basename;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky");
$modifier = new Basename("paradise.xml");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/path/to/the/paradise.xml"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\replace_basename</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/to/the/sky");
$newUri = Uri\replace_basename($uri, "paradise.xml");
echo $newUri; //display "http://www.example.com/path/to/the/paradise.xml"
~~~

### Updating path extension

Adds, update and or remove the path extension from the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\Extension;

$uri = Http::createFromString("http://www.example.com/export.html");
$modifier = new Extension("csv");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/export.csv"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\replace_extension</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/export.html");
$newUri = Uri\replace_extension($uri, 'csv');
echo $newUri; //display "http://www.example.com/export.csv"
~~~

### Add the path basepath

Adds the path basepath from the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AddBasePath;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky");
$modifier = new AddBasePath("/the/real");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/the/real/path/to/the/sky"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\add_basepath</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/to/the/sky");
$newUri = Uri\add_basepath($uri, '/the/real');
echo $newUri; //display "http://www.example.com/the/real/path/to/the/sky"
~~~

### Remove the path basepath

Removes the path basepath from the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveBasePath;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky");
$modifier = new RemoveBasePath("/path/to/the");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/sky"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\remove_basepath</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/to/the/sky");
$newUri = Uri\remove_basepath($uri, "/path/to/the");
echo $newUri; //display "http://www.example.com/sky"
~~~

### Appending path

Appends a path to the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AppendSegment;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new AppendSegment("and/above");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky/and/above"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\append_path</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/to/the/sky/");
$newUri = Uri\append_path($uri, 'and/above');
echo $newUri; //display "http://www.example.com/path/to/the/sky/and/above"
~~~

### Prepending segments

Prepends a path to the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\PrependSegment;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new PrependSegment("and/above");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/and/above/path/to/the/sky/and/above"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\prepend_path</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/to/the/sky/");
$newUri = Uri\prepend_path($uri, 'and/above');
echo $newUri; //display "http://www.example.com/and/above/path/to/the/sky/and/above"
~~~

### Replacing a path segment

Replaces a segment from the current URI path with a new path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\ReplaceSegment;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceSegment(3, "sea");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sea/"
~~~

<p class="message-info">This URI middleware supports negative offset</p>

The previous example can be rewritten using negative offset:

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\ReplaceSegment;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceSegment(-1, "sea");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sea"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\replace_segment</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/to/the/sky/");
$newUri = Uri\replace_segment($uri, -1, 'sea');
echo $newUri; //display "http://www.example.com/path/to/the/sea"
~~~

### Removing selected segments

Removes selected segments from the current URI path by providing the segments offset.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveSegments;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveSegments([1, 3]);
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/path/the/"
~~~

<p class="message-info">This URI middleware supports negative offset</p>

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveSegments;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveSegments([-1, -2]);
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com/path/the"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\remove_segments</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/to/the/sky/");
$newUri = Uri\remove_segments($uri, [1, 3]);
echo $newUri; //display "http://www.example.com/path/the/"
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
$newUri = $modifier->process($uri);
echo $newUri; //display "data:text/plain;charset=utf-8,Hello%20World!"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\replace_data_uri_parameters</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("data:text/plain;charset=US-ASCII,Hello%20World!");
$newUri = Uri\replace_data_uri_parameters($uri, "charset=utf-8");
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
$newUri = $modifier->process($uri);
echo $newUri; //display "data:text/plain;charset=US-ASCII;base64,SGVsbG8gV29ybGQh"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\path_to_binary</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("data:text/plain;charset=US-ASCII,Hello%20World!");
$newUri = Uri\path_to_binary($uri);
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
$newUri = $modifier->process($uri);
echo $newUri; //display "data:text/plain;charset=US-ASCII,Hello%20World!"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\path_to_ascii</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("data:text/plain;charset=US-ASCII;base64,SGVsbG8gV29ybGQh");
$newUri = Uri\path_to_ascii($uri);
echo $newUri; //display "data:text/plain;charset=US-ASCII,Hello%20World!"
~~~

## Host specific URI Middlewares

In addition to modifiying the URI host component, the middleware normalizes the host content.

### Transcoding the host to ascii

Transcodes the host into its ascii representation according to RFC3986:

~~~php
<?php

use GuzzleHttp\Psr7\Uri;
use League\Uri\Modifiers\HostToAscii;

$uri = new Uri("http://스타벅스코리아.com/to/the/sky/");
$modifier = new HostToAscii();
$newUri = $modifier->process($uri);
echo get_class($newUri); //display \GuzzleHttp\Psr7\Uri
echo $newUri; //display "http://xn--oy2b35ckwhba574atvuzkc.com/to/the/sky/"
~~~

<p class="message-notice">This middleware will have no effect on <strong>League URI objects</strong> as this conversion is done by default.</p>

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\host_to_ascii</code> is available</p>

~~~php
<?php

use GuzzleHttp\Psr7\Uri as GuzzleUri;
use League\Uri;

$uri = new GuzzleUri("http://스타벅스코리아.com/to/the/sky/");
$newUri = Uri\host_to_ascii($uri);
echo get_class($newUri); //display \GuzzleHttp\Psr7\Uri
echo $newUri; //display "http://xn--oy2b35ckwhba574atvuzkc.com/to/the/sky/"
~~~

### Transcoding the host to its IDN form

Transcodes the host into its idn representation according to RFC3986:

~~~php
<?php

use GuzzleHttp\Psr7\Uri;
use League\Uri\Modifiers\HostToUnicode;

$uriString = "http://xn--oy2b35ckwhba574atvuzkc.com/to/the/./sky/";
$uri = new Uri($uriString);
$modifier = new HostToUnicode();
$newUri = $modifier->process($uri);
echo get_class($newUri); //display \GuzzleHttp\Psr7\Uri
echo $newUri; //display "http://스타벅스코리아.com/to/the/sky/"
~~~

<p class="message-notice">This middleware will have no effect on <strong>League URI objects</strong> because the object always transcode the host component into its RFC3986/ascii representation.</p>

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\host_to_unicode</code> is available</p>

~~~php
<?php

use GuzzleHttp\Psr7\Uri as GuzzleUri;
use League\Uri;

$uri = new GuzzleUri("http://xn--oy2b35ckwhba574atvuzkc.com/to/the/./sky/");
$newUri = Uri\host_to_unicode($uri);
echo get_class($newUri); //display \GuzzleHttp\Psr7\Uri
echo $newUri; //display "http://스타벅스코리아.com/to/the/sky/"
~~~

### Updating the host registrable domain

Update the registrable domain of a given URI.

~~~php
<?php

use GuzzleHttp\Psr7\Uri;
use League\Uri\Modifiers\RegistrableDomain;

$uri = new Uri("http://www.example.com/foo/bar");
$modifier = new RegistrableDomain('bbc.co.uk');
$newUri = $modifier->process($uri);
echo get_class($newUri); //display \GuzzleHttp\Psr7\Uri
echo $newUri; //display "http://www.bbc.co.uk/foo/bar"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\replace_registrabledomain</code> is available</p>

~~~php
<?php

use GuzzleHttp\Psr7\Uri as GuzzleUri;
use League\Uri;

$uri = new GuzzleUri("http://www.example.com/foo/bar");
$newUri = Uri\replace_registrabledomain($uri, 'bbc.co.uk');
echo get_class($newUri); //display \GuzzleHttp\Psr7\Uri
echo $newUri; //display "http://www.bbc.co.uk/foo/bar"
~~~

### Updating the host subdomain

Update the subdomain part of a given URI.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\Subdomain;

$uri = new Http::createFromString("http://www.example.com/foo/bar");
$modifier = new Subdomain('shop');
$newUri = $modifier->process($uri);
echo $newUri; //display "http://shop.example.com/foo/bar"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\replace_subdomain</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/foo/bar");
$newUri = Uri\replace_subdomain($uri, 'shop');
echo $newUri; //display "http://shop.example.com/foo/bar"
~~~

### Removing Zone Identifier

Removes the host zone identifier if present

~~~php
<?php

use Zend\Diactoros\Uri;
use League\Uri\Modifiers\RemoveZoneIdentifier;

$uriString = 'http://[fe80::1234%25eth0-1]/path/to/the/sky.php';
$uri = new Uri($uriString);
$modifier = new RemoveZoneIdentifier();
$newUri = $modifier->process($uri);
echo get_class($newUri); //display \Zend\Diactoros\Uri
echo $newUri; //display 'http://[fe80::1234]/path/to/the/sky.php'
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\remove_zone_id</code> is available</p>

~~~php
<?php

use Zend\Diactoros\Uri as DiactorosUri;
use League\Uri\Modifiers\RemoveZoneIdentifier;

$uri = new DiactorosUri('http://[fe80::1234%25eth0-1]/path/to/the/sky.php');
$newUri = Uri\remove_zone_id($uri);
echo get_class($newUri); //display \Zend\Diactoros\Uri
echo $newUri; //display 'http://[fe80::1234]/path/to/the/sky.php'
~~~

### Adding the root label

Adds the root label if not present

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AddRootLabel;

$uriString = 'http://example.com:83';
$uri = Http::createFromString($uriString);
$modifier = new AddRootLabel();
$newUri = $modifier->process($uri);
echo $newUri; //display 'http://example.com.:83'
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\add_root_label</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create('http://example.com:83');
$newUri = Uri\add_root_label($uri);
echo $newUri; //display 'http://example.com.:83'
~~~

### Removing the root label

Removes the root label if present

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveRootLabel;

$uriString = 'http://example.com.#yes';
$uri = Http::createFromString($uriString);
$modifier = new RemoveRootLabel();
$newUri = $modifier->process($uri);
echo $newUri; //display 'http://example.com#yes'
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\remove_root_label</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create('http://example.com.#yes');
$newUri = Uri\remove_root_label($uri);
echo $newUri; //display 'http://example.com#yes'
~~~

### Appending labels

Appends a host to the current URI host.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AppendLabel;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new AppendLabel("fr");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://www.example.com.fr/path/to/the/sky/"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\append_host</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/to/the/sky/");
$newUri = Uri\append_host($uri, 'fr');
echo $newUri; //display "http://www.example.com.fr/path/to/the/sky/"
~~~

### Prepending labels

Prepends a host to the current URI path.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\PrependLabel;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new PrependLabel("shop");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://shop.www.example.com/path/to/the/sky/and/above"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\prepend_host</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/to/the/sky/");
$newUri = Uri\prepend_host($uri, 'shop');
echo $newUri; //display "http://shop.www.example.com/path/to/the/sky/and/above"
~~~

### Replacing host label

Replaces a label from the current URI host with a host.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\ReplaceLabel;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceLabel(2, "admin.shop");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://admin.shop.example.com/path/to/the/sky"
~~~

<p class="message-notice">The host is considered as a hierarchical component, labels are indexed from right to left according to host RFC</p>

<p class="message-info">This URI middleware supports negative offset</p>

The previous example can be rewritten using negative offset:

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\ReplaceLabel;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceLabel(-1, "admin.shop");
$newUri = $modifier->process($uri);
echo $newUri; //display "http://admin.shop.example.com/path/to/the/sky"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\replace_label</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/to/the/sky/");
$newUri = Uri\replace_label($uri, -1, 'admin.shop');
echo $newUri; //display "http://admin.shop.example.com/path/to/the/sky"
~~~

### Removing selected labels

Removes selected labels from the current URI host. Labels are indicated using an array containing the labels offsets.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveLabels;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveLabels([2]);
$newUri = $modifier->process($uri);
echo $newUri; //display "http://example.com/path/the/sky/"
~~~

<p class="message-notice">The host is considered as a hierarchical component, labels are indexed from right to left according to host RFC</p>

<p class="message-info">This URI middleware supports negative offset</p>

The previous example can be rewritten using negative offset:

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveLabels;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveLabels([-1]);
$newUri = $modifier->process($uri);
echo $newUri; //display "http://example.com/path/the/sky/"
~~~

<p class="message-info">Since version <code>1.1.0</code> The alias function <code>Uri\remove_labels</code> is available</p>

~~~php
<?php

use League\Uri;

$uri = Uri\create("http://www.example.com/path/to/the/sky/");
$newUri = Uri\remove_labels($uri, [2]);
echo $newUri; //display "http://example.com/path/the/sky/"
~~~