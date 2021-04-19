---
layout: default
title: URI partial modifiers
---

Query modifiers
=====

The following modifiers update and normalize the URI query component. 

<p class="message-notice">Because each modification is done after parsing and building, the 
resulting query string may update the component character encoding. These changes are expected because of 
the rules governing parsing and building query string.</p>

## UriModifier::sortQuery

Sorts the query according to its key values. The sorting rules are the same uses by WHATWG `URLSearchParams::sort` method.

~~~php
$uriString = "http://example.com/?kingkong=toto&foo=bar%20baz&kingkong=ape";
$uri = Http::createFromString($uriString);
$newUri = UriModifier::sortQuery($uri);

echo $uri->getQuery();    //display "kingkong=toto&foo=bar%20baz&kingkong=ape"
echo $newUri->getQuery(); //display "kingkong=toto&kingkong=ape&foo=bar%20baz"
~~~

## UriModifier::mergeQuery

Merges a submitted query string to the URI object to be modified. When merging two query strings with the same key value the submitted query string value takes precedence over the URI query string value.

~~~php
$uriString = "http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3";
$uri = Http::createFromString($uriString);
$newUri = UriModifier::mergeQuery($uri, 'kingkong=godzilla&toto');

echo $uri->getQuery();    //display "kingkong=toto&foo=bar+baz"
echo $newUri->getQuery(); //display "kingkong=godzilla&foo=bar%20baz&toto"
~~~

## UriModifier::appendQuery

Appends a submitted query string to the URI object to be modified. When appending two query strings with the same key value the submitted query string value is added to the return query string without modifying the URI query string value.

~~~php
$uriString = "http://example.com/test.php?kingkong=toto&foo=bar+baz#doc3";
$uri = Http::createFromString($uriString);
$newUri = UriModifier::appendQuery($uri, 'kingkong=godzilla&toto');

echo $uri->getQuery();    //display "kingkong=toto&foo=bar+baz"
echo $newUri->getQuery(); //display "kingkong=toto&kingkong=godzilla&foo=bar%20baz&toto"
~~~

## UriModifier::removePairs

Removes query pairs from the current URI query string by providing the pairs key.

~~~php
$uriString = "http://example.com/test.php?kingkong=toto&foo=bar+baz&bar=baz#doc3")
$uri = Http::createFromString($uriString);
$newUri = UriModifier::removePairs($uri, 'foo', 'bar');

echo $uri->getQuery();    //display "kingkong=toto&foo=bar+baz&bar=baz"
echo $newUri->getQuery(); //display "kingkong=toto"
~~~

## UriModifier::removeParams

Removes query params from the current URI query string by providing the param name. The removal preserves mangled key params.

~~~php
$uriString = "http://example.com/test.php?kingkong=toto&fo.o=bar&fo_o=bar";
$uri = Http::createFromString($uriString);
$newUri = UriModifier::removeParams($uri, 'fo.o');

echo $uri->getQuery();    //display "kingkong=toto&fo.o=bar&fo_o=bar"
echo $newUri->getQuery(); //display "kingkong=toto&fo_o=bar"
~~~
