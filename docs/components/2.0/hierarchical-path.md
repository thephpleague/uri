---
layout: default
title: The Hierarchical Path component
---

# The Hierarchical Path component

The library provides a `HierarchicalPath` class to ease HTTP like path creation and manipulation. This URI component object exposes :

- the [package common API](/components/2.0/api/)
- the [path common API](/components/2.0/path)

It also provides specific methods to work with segments-type URI path components.

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">When a modification fails a <code>League\Uri\Contracts\UriException</code> exception is thrown.</p>

## Instantiation

### Using the default constructor

~~~php
<?php
public HierarchicalPath::__construct(?string $content = null): void
~~~

<p class="message-notice">submitted string is normalized to be <code>RFC3986</code> compliant.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Components\Exception</code> exception is thrown.</p>

### Using a string

A string or an PHP object exposing a `__toString` method can be used to instantiate a new object with the following named constructor.

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = HierarchicalPath::createFromString('path/to/the/sky');
$path->getContent(); //returns 'path/to/the/sky'
~~~

### Using a collection of path segments.

A path is a collection of segment delimited by the path delimiter `/`. So it is possible to create a `HierarchicalPath` object using a collection of segments with the `HierarchicalPath::createFromSegments` method.

The method expects at most 2 arguments:

- The first required argument must be a collection of segments (an `array` or a `Traversable` object)
- The second optional argument, a `HierarchicalPath` constant, tells whether this is a rootless path or not:
    - `HierarchicalPath::IS_ABSOLUTE`: the created object will represent an absolute path;
    - `HierarchicalPath::IS_RELATIVE`: the created object will represent a rootless path;

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$relative_path =  HierarchicalPath::createFromSegments(['shop', 'example', 'com']);
echo $relative_path; //display 'shop/example/com'

$absolute_path = HierarchicalPath::createFromSegments(['shop', 'example', 'com'], Path::IS_ABSOLUTE);
echo $absolute_path; //display '/shop/example/com'

$end_slash = HierarchicalPath::createFromSegments(['shop', 'example', 'com', ''], Path::IS_ABSOLUTE);
echo $end_slash; //display '/shop/example/com/'
~~~

<p class="message-info">To force the end slash when using the <code>Path::createFromSegments</code> method you need to add an empty string as the last member of the submitted array.</p>

## Manipulating the path as a filesystem path

The `HierarchicalPath` allows you to access and manipulate the path as if it was a filesystem path.

### Accessing the path

~~~php
<?php

public HierarchicalPath::getDirname(void): string
public HierarchicalPath::getBasename(void): string
public HierarchicalPath::getExtension(void): string
~~~

#### Usage

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = HierarchicalPath::createFromString('/path/to/the/sky.txt');
$path->getExtension(); //return 'txt'
$path->getBasename();  //return 'sky.txt'
$path->getDirname();   //return '/path/to/the'
~~~

### Modifying the path

~~~php
<?php

public HierarchicalPath::withDirname(string $dirname): self
public HierarchicalPath::withBasename(string $basename): self
public HierarchicalPath::withExtension(string $extension): self
~~~

<p class="message-warning"><code>withExtension</code> will throw an <code>League\Uri\Components\Exception</code> exception if the extension contains the path delimiter.</p>

#### Usage

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = HierarchicalPath::createFromString('/path/to/the/sky.txt;foo=bar');
$new_path = $path
    ->withDirname('/foo')
    ->withExtension('csv');
echo $new_path; // display /foo/sky.csv;foo=bar

$alt_path = $path
    ->withBasename('paradise.html');
echo $alt_path; // display /path/to/the/paradise.html
~~~

## The path as a collection of segments

~~~php
<?php
const HierarchicalPath::IS_RELATIVE = 0;
const HierarchicalPath::IS_ABSOLUTE = 1;
public HierarchicalPath::isAbsolute(void): bool
public HierarchicalPath::segments(void): array
public HierarchicalPath::get(int $offset, $default = null): mixed
public HierarchicalPath::keys([string $segment]): array
public HierarchicalPath::count(void): int
public HierarchicalPath::getIterator(void): ArrayIterator
public HierarchicalPath::prepend(string $path): self
public HierarchicalPath::append(string $path): self
public HierarchicalPath::withSegment(int $offset, string $path): self
public HierarchicalPath::withoutSegment(int $offsets, int ...$offsets): self
public HierarchicalPath::withoutEmptySegment(): self
~~~

### Accessing the path segments

A path can be represented as an array of its internal segments. Through the use of the `HierarchicalPath::getSegments` method the class returns the object array representations.

<p class="message-info">A path ending with a slash will have an empty string as the last member of its array representation.</p>

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = new HierarchicalPath('/path/to/the/sky');
$path->segments(); //return ['path', 'to', 'the', 'sky'];

$absolute_path = new HierarchicalPath('/path/to/the/sky/');
$absolute_path->segments(); //return ['path', 'to', 'the', 'sky', ''];

$relative_path = new HierarchicalPath('path/to/the/sky/');
$relative_path->segments(); //return ['path', 'to', 'the', 'sky', ''];
~~~

The class implements PHP's `Countable` and `IteratorAggregate` interfaces. This means that you can count the number of segments and use the `foreach` construct to iterate overs them.

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = HierarchicalPath::createFromString('/path/to/the/sky');
count($path); //return 4
foreach ($path as $offset => $segment) {
    //do something meaningful here
}
~~~

### Accessing the segments offset

If you are interested in getting all the segments offsets you can do so using the `HierarchicalPath::keys` method like shown below:

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = HierarchicalPath::createFromString('/path/to/the/sky');
$path->keys();        //return [0, 1, 2, 3];
$path->keys('sky');   //return [3];
$path->keys('gweta'); //return [];
~~~

The method returns all the segment keys, but if you supply an argument, only the keys whose segment value equals the argument are returned.

<p class="message-info">The supplied argument is decoded to enable matching the corresponding keys.</p>

### Accessing the segments content

If you are only interested in a given segment you can access it directly using the `HierarchicalPath::getSegment` method as show below:

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = HierarchicalPath::createFromString('/path/to/the/sky');
$path->get(0);         //return 'path'
$path->get(23);        //return null
$path->get(23, 'now'); //return 'now'
~~~

<p class="message-notice"><code>HierarchicalPath::getSegment</code> always returns the decoded representation.</p>

If the offset does not exists it will return the value specified by the optional second argument or `null`.

<p class="message-info"><code>HierarchicalPath::getSegment</code> supports negative offsets</code></p>

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = HierarchicalPath::createFromString('/path/to/the/sky');
$path->get(-1);         //return 'sky'
$path->get(-23);        //return null
$path->get(-23, 'now'); //return 'now'
~~~

## Manipulating the path segments

### Append segments

To append segments to the current object you need to use the `HierarchicalPath::append` method. This method accept a single argument which represents the data to be appended. This data can be a string, an object which implements the `__toString` method or another `HierarchicalPath` object:

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path    = HierarchicalPath::createFromString();
$newPath = $path->append('path')->append('to/the/sky');
$newPath->__toString(); //return path/to/the/sky
~~~

### Prepend segments

To prepend segments to the current path you need to use the `HierarchicalPath::prepend` method. This method accept a single argument which represents the data to be prepended. This data can be a string, an object which implements the `__toString` method or another `HierarchicalPath` object:

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path    = HierarchicalPath::createFromString();
$newPath = $path->prepend('sky')->prepend('path/to/the');
$newPath->__toString(); //return path/to/the/sky
~~~

### Replace segments

To replace a segment you must use the `HierarchicalPath::withSegment` method with the following arguments:

- `$offset` which represents the segment offset to remove if it exists.
- `$data` which represents the data to be inject.  This data can be a string, an object which implements the `__toString` method or another `HierarchicalPath` object.

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path    = HierarchicalPath::createFromString('/foo/example/com');
$newPath = $path->withSegment(0, 'bar/baz');
$newPath->__toString(); //return /bar/baz/example/com
~~~

<p class="message-info">Just like the <code>HierarchicalPath::getSegment</code> this method supports negative offset.</p>

<p class="message-notice">if the specified offset does not exists, no modification is performed and the current object is returned.</p>

### Remove segments

To remove segments from the current object and returns a new `HierarchicalPath` object without them you must use the `HierarchicalPath::withoutSegments` method. This method expects a single argument. This argument is an array containing a list of parameter names to remove.

~~~php
<?php

use League\Uri\Components\HierarchicalPath;

$path = new HierarchicalPath('/path/to/the/sky');
$newPath = $path->withoutSegment(0, 1);
$newPath->__toString(); //return '/the/sky'
~~~

<p class="message-info">Just like the <code>HierarchicalPath::getSegment</code> this method supports negative offset.</p>

<p class="message-notice">if the specified offset does not exists, no modification is performed and the current object is returned.</p>
