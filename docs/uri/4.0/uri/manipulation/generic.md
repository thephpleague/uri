---
layout: default
title: URI Modifiers which affect multiple URI components
redirect_from:
    - /4.0/uri/manipulation/generic/
---

# Generic URI Modifiers

Here's the documentation for the included URI modifiers which are modifying multiple URI components at once.

## Normalize a URI

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

## Resolving a relative URI

### Description

~~~php
<?php

public Resolve::__construct(mixed $uri)
~~~

The `Resolve` URI Modifier provides the mean for resolving an URI as a browser would for a relative URI. When performing URI resolution the returned URI is normalized according to RFC3986 rules. The uri to resolved must be another Uri object.

### Parameters

`$uri` **must be** a `League\Uri\Interfaces\Uri` or a `Psr\Http\Message` implemented object

### Example

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

## Relativize an URI

<p class="message-notice">New in <code>version 4.2</code></p>

### Description

~~~php
<?php

public Relativize::__construct(mixed $uri)
~~~

The `Relativize` URI Modifier provides the mean to construct a relative URI that when resolved against the same URI yields the same given URI. This modifier does the inverse of the Resolve modifier. The uri to relativize must be another Uri object.

### Parameters

`$uri` **must be** a `League\Uri\Interfaces\Uri` or a `Psr\Http\Message` implemented object

### Example

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\Relativize;
use League\Uri\Modifiers\Resolve;

require '/path/to/vendor/autoload.php';

$baseUri = Http::createFromString('http://www.example.com');
$relativizer = new Relativize($baseUri);
$resolver = new Resolve($baseUri);
$uri = Http::createFromString('http://www.example.com/?foo=toto#~typo');
$relativeUri = $relativizer($uri);
echo $relativeUri; // display "/?foo=toto#~typo
echo $resolver($relativeUri); // display 'http://www.example.com/?foo=toto#~typo'
~~~

<p class="message-notice">To be sure that both operations yield the expected results both URI must be normalized.</p>

## Applying multiple modifiers to a single URI

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
