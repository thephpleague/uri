---
layout: default
title: The Hierarchical Path component
---

# The Hierarchical Path component

The library provides a `League\Uri\Components\HierarchicalPath` class to ease complex path manipulation on a Hierarchical URI object like `http` scheme URI. The class exposes the same method as the default `Path` object with methods dedicated to manipulate hierarchical paths.

## Path creation

### Using a named constructor

A path is a collection of segment delimited by the path delimiter `/`. So it is possible to create a `HierarchicalPath` object using a collection of segments with the `HierarchicalPath::createFromSegments` method.

The method expects at most 2 arguments:

- The first required argument must be a collection of segments (an `array` or a `Traversable` object)
- The second optional argument, a `Uri\Path` constant, tells whether this is a rootless path or not:
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

## Path representations

### Collection representation

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

## Accessing Path content

### Countable and IteratorAggregate

The class provides several methods to works with its segments. The class implements PHP's `Countable` and `IteratorAggregate` interfaces. This means that you can count the number of segments and use the `foreach` construct to iterate overs them.

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky');
count($path); //return 4
foreach ($path as $offset => $segment) {
    //do something meaningful here
}
~~~

### Segment offsets

If you are interested in getting all the segments offsets you can do so using the `HierarchicalPath::keys` method like shown below:

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky');
$path->keys();        //return [0, 1, 2, 3];
$path->keys('sky');   //return [3];
$path->keys('gweta'); //return [];
~~~

The method returns an array containing all the segments offsets. If you supply an argument, only the offsets whose segment value equals the argument are returned.

### Segment content

If you are only interested in a given segment you can access it directly using the `HierarchicalPath::getSegment` method as show below:

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky');
$path->getSegment(0);         //return 'path'
$path->getSegment(23);        //return null
$path->getSegment(23, 'now'); //return 'now'
~~~

The method returns the value of a specific offset. If the offset does not exists it will return the value specified by the optional second argument or `null`.


This method supports negative offsets

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky');
$path->getSegment(-1);         //return 'sky'
$path->getSegment(-23);        //return null
$path->getSegment(-23, 'now'); //return 'now'
~~~

### The basename

To ease working with path you can get the trailing segment of a path by using the `HierarchicalPath::getBasename` method, this method takes no argument. If the segment ends with an extension, it will be included in the output.

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky');
$path->getBasename(); //return 'sky'

$alt_path = new Path('path/to/the/sky.html');
$alt_path->getBasename(); //return 'sky.html'
~~~

### The basename extension

If you are only interested in getting the basename extension, you can directly call the `HierarchicalPath::getExtension` method. This method, which takes no argument, returns the trailing segment extension as a string if present or an empty string. The leading `.` delimiter is removed from the method output.

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky');
$path->getBasename(); //return ''

$path = new Path('/path/to/file.csv');
$path->getExtension(); //return 'csv';
~~~

The `getExtension` method takes into account path parameters:

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky.txt;foo=bar,baz');
$path->getBasename();  //return 'sky.txt;foo=bar,baz'
$path->getExtension(); //return 'txt';
~~~

### The dirname

Conversely, you can get the path dirname by using the `HierarchicalPath::getDirname` method, this method takes no argument and works like PHP's `dirname` function.

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path = new Path('/path/to/the/sky.txt');
$path->getExtension(); //return 'txt'
$path->getBasename();  //return 'sky.txt'
$path->getDirname();   //return '/path/to/the'
~~~

## Path normalization

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">When a modification fails a <code>InvalidArgumentException</code> exception is thrown.</p>

Out of the box, the `HierarchicalPath` object operates a number of non destructive normalizations. For instance, the path is correctly URI encoded against the RFC3986 rules.

## Modifying Path

### Path extension manipulation

You can easily change or remove the extension from the path basename using the `HierarchicalPath::withExtension` method.

<p class="message-info">No update will be made if the <code>basename</code> is empty</p>

<p class="message-warning">This method will throw an <code>InvalidArgumentException</code> exception if the extension contains the path delimiter.</p>

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path    = new Path('/path/to/the/sky');
$newPath = $path->withExtension('csv');
echo $newPath->getExtension(); //displays csv;
echo $path->getExtension();    //displays '';
~~~

<p class="message-notice">This method is used by the URI modifier <code>Extension</code></p>

### Append segments

To append segments to the current object you need to use the `HierarchicalPath::append` method. This method accept a single argument which represents the data to be appended. This data can be a string, an object which implements the `__toString` method or another `HierarchicalPath` object:

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path    = new Path();
$newPath = $path->append(new Path('path'))->append('to/the/sky');
$newPath->__toString(); //return path/to/the/sky
~~~

<p class="message-notice">This method is used by the URI modifier <code>AppendSegment</code></p>

### Prepend segments

To prepend segments to the current path you need to use the `HierarchicalPath::prepend` method. This method accept a single argument which represents the data to be prepended. This data can be a string, an object which implements the `__toString` method or another `HierarchicalPath` object:

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path    = new Path();
$newPath = $path->prepend(new Path('sky'))->prepend(new Path('path/to/the'));
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
$newPath = $path->replace(0, new Path('bar/baz'));
$Path->__toString(); //return /bar/baz/example/com
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

### Filter segments

You can filter the `HierarchicalPath` object using the `HierarchicalPath::filter` method. Filtering is done using the same arguments as PHP's `array_filter`.

You can filter the path according to its segments values:

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path    = new Path('/foo/bar/yolo/');
$newPath = $path->filter(function ($value) {
    return ! empty($value);
});
echo $newPath; //displays '/foo/bar/yolo'
~~~

You can filter the path according to its segments key.

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path    = new Path('/foo/bar/yolo/');
$newPath = $query->filter(function ($value) {
    return 1 != $value;
}, ARRAY_FILTER_USE_KEY);
echo $newPath; //displays '/foo/yolo'
~~~

You can filter the path according to its segment value and key.

~~~php
<?php

use League\Uri\Components\HierarchicalPath as Path;

$path    = new Path('/foo/bar/yolo/');
$newPath = $query->filter(function ($value, $key) {
    return 1 != $key && strpos($value, 'l') !== false;
}, ARRAY_FILTER_USE_BOTH);
echo $newPath; //displays '/yolo'
~~~

The methods use the same value as `array_filter`.

<p class="message-notice">This method is used by the URI modifier <code>FilterSegments</code></p>
