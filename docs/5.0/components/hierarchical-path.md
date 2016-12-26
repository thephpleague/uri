---
layout: default
title: The Hierarchical Path component
---

# The Hierarchical Path component

The library provides a `HierarchicalPath` class to ease HTTP like path creation and manipulation. This URI component object exposes :

- the [package common API](/5.0/components/api/)
- the [path common API](/5.0/components/path)

but also provide specific methods to work with segments-type URI path components.

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">When a modification fails a <code>InvalidArgumentException</code> exception is thrown.</p>

## Manipulating the path as a filesystem path

THe `HierarchicalPath` allows you to access and manipulate the path as if it was a filesystem path.

### The parent directory path

~~~php
<?php

public HierarchicalPath::getDirname(void): string
public HierarchicalPath::withDirname(string $dirname): self
~~~

`getDirname` returns the path parent's directory while `withDirname` returns a new instance with the modified parent's directory path.

<p class="message-notice">This method is used by the URI modifier <code>Dirname</code></p>

### The path basename

~~~php
<?php


public HierarchicalPath::getBasename(void): string
public HierarchicalPath::withBasename(string $basename): self
~~~

`getBasename` returns the complete trailing segment of a path, including its extension optional path parameters. You can change the segment content using the complementary `withBasename` method. This method expects a string and returns a new instance with the modified basename.

<p class="message-notice">This method is used by the URI modifier <code>Basename</code></p>

### The basename extension

~~~php
<?php

public HierarchicalPath::getExtension(void): string
public HierarchicalPath::withExtension(string $extension): self
~~~

If you are only interested in getting the basename extension, you can directly call the `getExtension` method. The method only returns the basename extension as a string if present. The leading `.` delimiter is removed from the method output. The complementary method `withExtension` is provided to modify the basename extension. Both methods do not interact with the path parameters if present.

<p class="message-warning"><code>withExtension</code> will throw an <code>InvalidArgumentException</code> exception if the extension contains the path delimiter.</p>

<p class="message-notice">This method is used by the URI modifier <code>Extension</code></p>

### Usage

#### Accessing the properties

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky.txt');
$path->getExtension(); //return 'txt'
$path->getBasename();  //return 'sky.txt'
$path->getDirname();   //return '/path/to/the'
~~~

#### Modifying the path

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky.txt;foo=bar');
$new_path = $path
    ->withDirname('/foo')
    ->withExtension('csv');
echo $new_path; // display /foo/sky.csv;foo=bar

$alt_path = $path
    ->withBasename('paradise.html');
echo $alt_path; // display /foo/paradise.html
~~~

## The path as a segments collection

### HierarchicalPath::createFromSegments

A path is a collection of segment delimited by the path delimiter `/`. So it is possible to create a `HierarchicalPath` object using a collection of segments with the `HierarchicalPath::createFromSegments` method.

The method expects at most 2 arguments:

- The first required argument must be a collection of segments (an `array` or a `Traversable` object)
- The second optional argument, a `HierarchicalPath` constant, tells whether this is a rootless path or not:
    - `HierarchicalPath::IS_ABSOLUTE`: the created object will represent an absolute path;
    - `HierarchicalPath::IS_RELATIVE`: the created object will represent a rootless path;

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$relative_path =  Path::createFromSegments(['shop', 'example', 'com']);
echo $relative_path; //display 'shop/example/com'

$absolute_path = Path::createFromSegments(['shop', 'example', 'com'], Path::IS_ABSOLUTE);
echo $absolute_path; //display '/shop/example/com'

$end_slash = Path::createFromSegments(['shop', 'example', 'com', ''], Path::IS_ABSOLUTE);
echo $end_slash; //display '/shop/example/com/'
~~~

<p class="message-info">To force the end slash when using the <code>Path::createFromSegments</code> method you need to add an empty string as the last member of the submitted array.</p>

### Accessing the path segments

A path can be represented as an array of its internal segments. Through the use of the `HierarchicalPath::getSegments` method the class returns the object array representations.

<p class="message-info">A path ending with a slash will have an empty string as the last member of its array representation.</p>

<p class="message-warning">Once in array representation you can not distinguish a relative from a absolute path</p>

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky');
$path->getSegments(); //return ['path', 'to', 'the', 'sky'];

$absolute_path = new Path('/path/to/the/sky/');
$absolute_path->getSegments(); //return ['path', 'to', 'the', 'sky', ''];

$relative_path = new Path('path/to/the/sky/');
$relative_path->getSegments(); //return ['path', 'to', 'the', 'sky', ''];
~~~

The class implements PHP's `Countable` and `IteratorAggregate` interfaces. This means that you can count the number of segments and use the `foreach` construct to iterate overs them.

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky');
count($path); //return 4
foreach ($path as $offset => $segment) {
    //do something meaningful here
}
~~~

### Accessing the segments offset

If you are interested in getting all the segments offsets you can do so using the `HierarchicalPath::keys` method like shown below:

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky');
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

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky');
$path->getSegment(0);         //return 'path'
$path->getSegment(23);        //return null
$path->getSegment(23, 'now'); //return 'now'
~~~

<p class="message-notice"><code>HierarchicalPath::getSegment</code> always returns the decoded representation.</p>

If the offset does not exists it will return the value specified by the optional second argument or `null`.

<p class="message-info"><code>HierarchicalPath::getSegment</code> supports negative offsets</code></p>

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky');
$path->getSegment(-1);         //return 'sky'
$path->getSegment(-23);        //return null
$path->getSegment(-23, 'now'); //return 'now'
~~~

## Manipulating the path segments

### Append segments

To append segments to the current object you need to use the `HierarchicalPath::append` method. This method accept a single argument which represents the data to be appended. This data can be a string, an object which implements the `__toString` method or another `HierarchicalPath` object:

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path    = new Path();
$newPath = $path->append('path')->append('to/the/sky');
$newPath->__toString(); //return path/to/the/sky
~~~

<p class="message-notice">This method is used by the URI modifier <code>AppendSegment</code></p>

### Prepend segments

To prepend segments to the current path you need to use the `HierarchicalPath::prepend` method. This method accept a single argument which represents the data to be prepended. This data can be a string, an object which implements the `__toString` method or another `HierarchicalPath` object:

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path    = new Path();
$newPath = $path->prepend('sky')->prepend(path/to/the');
$newPath->__toString(); //return path/to/the/sky
~~~

<p class="message-notice">This method is used by the URI modifier<code>PrependSegment</code></p>

### Replace segments

To replace a segment you must use the `HierarchicalPath::replace` method with the following arguments:

- `$offset` which represents the segment offset to remove if it exists.
- `$data` which represents the data to be inject.  This data can be a string, an object which implements the `__toString` method or another `HierarchicalPath` object.

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path    = new Path('/foo/example/com');
$newPath = $path->replace(0, 'bar/baz');
$newPath->__toString(); //return /bar/baz/example/com
~~~

<p class="message-notice">if the specified offset does not exists, no modification is performed and the current object is returned.</p>

<p class="message-notice">This method is used by the URI modifier<code>ReplaceSegment</code></p>

### Remove segments

To remove segments from the current object and returns a new `HierarchicalPath` object without them you must use the `HierarchicalPath::without` method. This method expects a single argument. This argument is an array containing a list of parameter names to remove.

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky');
$newPath = $path->without([0, 1]);
$newPath->__toString(); //return '/the/sky'
~~~


<p class="message-notice">if the specified offset does not exists, no modification is performed and the current object is returned.</p>

<p class="message-notice">This method is used by the URI modifier<code>RemoveSegments</code></p>