---
layout: default
title: URI Modifiers which affect multiple URI components
---

# Generic URI Modifiers

## Resolving a relative URI

The `Resolve` URI Modifier provides the mean for resolving an URI as a browser would for an anchor tag. When performing URI resolution the returned URI is normalized according to RFC3986 rules. The uri to resolved must be another Uri object.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\Resolve;

$baseUri     = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$relativeUri = HttpUri::createFromString("./p#~toto");
$modifier    = new Resolve($baseUri);
$newUri = $modifier->__invoke($relativeUri);
echo $newUri; //displays "http://www.example.com/hello/p#~toto"
~~~

## Applying multiple modifiers to a single URI

Since all modifiers returns a URI object instance it is possible to chain them together. To ease this chaining the package comes bundle with the `League\Uri\Modifiers\Pipeline` class. The class uses the pipeline pattern to modify the URI by passing the results from one modifier to the next one. 

The `League\Uri\Modifiers\Pipeline` uses two methods:

- `Pipeline::pipe` to attach a URI modifier following the *First In First Out* rule.
- `Pipeline::process` to apply sequencially each attached URI modifier to the submitted URI object. 


~~~php
use League\Uri\Modifiers\HostToAscii;
use League\Uri\Modifiers\KsortQuery;
use League\Uri\Modifiers\Pipeline;
use League\Uri\Modifiers\RemoveDotSegments;
use League\Uri\Schemes\Http as HttpUri;

$origUri = HttpUri::createFromString("http://스타벅스코리아.com/to/the/sky/");
$origUri2 = HttpUri::createFromString("http://xn--oy2b35ckwhba574atvuzkc.com/path/../to/the/./sky/");

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

## Normalize a URI

To help wil URI objects comparison, the  <code>League\Uri\Modifiers\Normalize</code> URI modifier is introduce to normalize URI according to the following rules:

- The host component is converted into their ASCII representation;
- The path component is normalized by removing dot segments as per RFC3986;
- The query component is sorted according to its key offset;
- The scheme component is lowercased;

If you normalized two URI objects it become easier to compare them to determine if they are referring to the same resource:

~~~php
use League\Uri\Modifiers\Normalize;
use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString("http://스타벅스코리아.com/to/the/sky/");
$altUri = HttpUri::createFromString("http://xn--oy2b35ckwhba574atvuzkc.com/path/../to/the/./sky/");
$modifier = new Normalize();

$newUri    = $modifier->__invoke($uri);
$newAltUri = $modifier->__invoke($altUri);

var_dump($newUri->__toString() === $newAltUri->__toString()); //return true
~~~
