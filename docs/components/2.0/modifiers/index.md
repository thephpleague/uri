---
layout: default
title: URI partial modifiers
---

URI modifiers
=======

## Example

For instance here's how you would update the query string from a given URI object:

~~~php
<?php

use GuzzleHttp\Psr7\Uri;

$uriString = "http://www.example.com?fo.o=toto#~typo";
$queryToMerge = 'fo.o=bar&taz=';

$uri = new Uri($uriString);
parse_str($uri->getQuery(), $params);
parse_str($queryToMerge, $paramsToMerge);
$query = http_build_query(
    array_merge($params, $paramsToMerge),
    '',
    '&',
    PHP_QUERY_RFC3986
);

$newUri = $uri->withQuery($query);
echo $newUri; // display http://www.example.com?fo_o=bar&taz=#~typo
~~~

Using the provided `League\Uri\UriModifier::mergeQuery` modifier the code becomes

~~~php
<?php

use GuzzleHttp\Psr7\Uri;
use League\Uri\UriModifier;

$uriString = "http://www.example.com?fo.o=toto#~typo";
$queryToMerge = 'fo.o=bar&taz=';

$uri = new Uri($uriString);
$newUri = UriModifier::mergeQuery($uri, $queryToMerge);

echo get_class($newUri); // returns \GuzzleHttp\Psr7\Uri
echo $newUri; // display http://www.example.com?fo.o=bar&taz=#~typo
~~~

In addition to merging the query to the URI, `mergeQuery` has:

- enforced `RFC3986` encoding through out the modifications;
- not mangle your data during merging;
- returned an URI object of the same class as the one it received;

## Definition

An URI modifier is a method or a function which provides a convenient mechanism for filtering and manipulating an URI object.
The only **hard** requirement is that the modifier **MUST** returns an URI instance of the same type that the one it received.

## References

Under the hood the `UriModifier` class uses the [URI components objects](/components/2.0/api/) to apply changes to the submitted URI object.

- [Query modifiers](/components/2.0/modifiers/query/)
- [Host modifiers](/components/2.0/modifiers/host/)
- [Path modifiers](/components/2.0/modifiers/path/)
