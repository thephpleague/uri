---
layout: default
title: Path Component
---

Path Component
=======

URI path component objects are modelled depending on the URI as such each URI scheme specific must implement its own path object. To ease Path usage, the package comes with a generic Path object as well as two more specialized Path objects. All Path objects expose the following methods:

~~~php
<?php

public Path::isEmpty(void): bool
public Path::isAbsolute(void): bool
public Path::withLeadingSlash(void): self
public Path::withoutLeadingSlash(void): self
public Path::withoutDotSegments(void): self
public Path::withTrailingSlash(void): self
public Path::withoutTrailingSlash(void): self
public Path::withoutEmptySegments(void): self
~~~

<p class="message-notice">According to <code>RFC3986</code>, the component content can not be equal to <code>null</code> therefore <code>Path::isDefined</code> always returns <code>true</code></p>

<p class="message-warning">Because path are scheme specific, some methods may trigger an <code>InvalidArgumentException</code> if for a given scheme the path does not support the given modification</p>


## Path properties

### Absolute, rootless or empty

- A path is considered absolute only if it starts with the path separator `/`, otherwise it is considered as being relative or rootless. At any given time you can test your path status using the `Path::isAbsolute` method.

- A path can never be `null` or undefined. But a path can be empty. You can test the path status with the `Path::isEmpty` method.

~~~php
<?php

use League\Uri\Components\Path;

$relative_path = new Path('bar/baz');
echo $relative_path; //displays 'bar/baz'
$relative_path->isAbsolute(); //return false;
$relative_path->isEmpty(); //return false;

$absolute_path = new Path('/bar/baz');
echo $absolute_path; //displays '/bar/baz'
$absolute_path->isAbsolute(); //return true;
$absolute_path->isEmpty(); //return false;

$empty_path = new Path();
echo $absolute_path; //displays ''
$empty_path->isAbsolute(); //return false;
$empty_path->isEmpty(); //return true;
~~~

### Path with or without a trailing slash

The `Path` object can tell you whether the current path ends with a slash or not using the `Path::hasTrailingSlash` method. This method takes no argument and return a boolean.

~~~php
<?php

use League\Uri\Components\Path;

$path = new Path('/path/to/the/sky.txt');
$path->hasTrailingSlash(); //return false

$altPath = new Path('/path/');
$altPath->hasTrailingSlash(); //return true
~~~

## Path modifications

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">When a modification fails a <code>InvalidArgumentException</code> exception is thrown.</p>

Out of the box, the `Path` object operates a number of non destructive normalizations. For instance, the path is correctly URI encoded against the RFC3986 rules.

### Removing dot segments

To remove dot segment as per [RFC3986](https://tools.ietf.org/html/rfc3986#section-6) you need to explicitly call the `Path::withoutDotSegments` method as the result can be destructive. The method takes no argument and returns a new `Path` object which represents the current object without dot segments.

~~~php
<?php

use League\Uri\Components\Path;

$path = new Path('path/to/./the/../the/sky%7bfoo%7d');
$newPath = $raw_path->withoutDotSegments();
echo $path;                   //displays 'path/to/./the/../the/sky%7bfoo%7d'
echo $newPath;                //displays 'path/to/the/sky%7Bfoo%7D'
$newPath->sameValueAs($path); //returns false;
~~~

<p class="message-notice">This method is used by the URI Modifier <code>RemoveDotSegments</code></p>

### Removing empty segments

Sometimes your path may contain multiple adjacent delimiters. Since removing them may result in a semantically different URI, this normalization can not be applied by default. To remove adjacent delimiters you can call the `Path::withoutEmptySegments` method which convert you path as described below:

~~~php
<?php

use League\Uri\Components\Path;

$path    = new Path("path////to/the/sky//");
$newPath = $path->withoutEmptySegments();
echo $path;                   //displays 'path////to/the/sky//'
echo $newPath;                //displays 'path/to/the/sky/'
$newPath->sameValueAs($path); //returns false;
~~~

<p class="message-notice">This method is used by the URI Modifier <code>RemoveEmptySegments</code></p>

### Manipulating the trailing slash

Depending on your context you may want to add or remove the path trailing slash. In order to do so the `Path` object uses two methods which accept no argument.

`Path::withoutTrailingSlash` will remove the ending slash of your path only if a slash is present.

~~~php
<?php

use League\Uri\Components\Path;

$path    = new Path("path/to/the/sky/");
$newPath = $path->withoutTrailingSlash();
echo $path;     //displays 'path/to/the/sky/'
echo $newPath;  //displays 'path/to/the/sky'
~~~

<p class="message-notice">This method is used by the URI Modifier <code>RemoveTrailingSlash</code></p>

Conversely, `Path::withTrailingSlash` will append a slash at the end of your path only if no slash is already present.

~~~php
<?php

use League\Uri\Components\Path;

$path    = new Path("/path/to/the/sky");
$newPath = $path->withTrailingSlash();
echo $path;    //displays '/path/to/the/sky'
echo $newPath; //displays '/path/to/the/sky/'
~~~

<p class="message-notice">This method is used by the URI Modifier <code>AddTrailingSlash</code></p>

### Manipulating the leading slash

Conversely, to convert the path type the `Path` object uses two methods which accept no argument.

`Path::withoutLeadingSlash` will convert an absolute path into a relative one by removing the path leading slash if present.

~~~php
<?php

use League\Uri\Components\Path;

$path    = new Path("path/to/the/sky/");
$newPath = $path->withoutTrailingSlash();
echo $path;    //displays 'path/to/the/sky/'
echo $newPath; //displays 'path/to/the/sky'
~~~

<p class="message-notice">This method is used by the URI Modifier <code>RemoveLeadingSlash</code></p>

`Path::withLeadingSlash` will convert an relative path into a absolute one by prepending the path with a slash if none is present.

~~~php
<?php

use League\Uri\Components\Path;

$path    = new Path("/path/to/the/sky");
$newPath = $path->withTrailingSlash();
echo $raw_path; //displays '/path/to/the/sky'
echo $newPath;  //displays '/path/to/the/sky/'
~~~

<p class="message-notice">This method is used by the URI Modifier <code>AddLeadingSlash</code></p>

## Specialized Path Object

What makes an URI specific apart from the scheme is how the path is parse and manipulated. This simple path class although functional will not ease parsing a Data URI path or a FTP Uri path. That's why the library comes bundles with two specialized Path objects that extend the current object by adding more specific methods in accordance to the path usage:

- the [HierarchicalPath](/dev-master/components/hierarchical-path/) object to work with Hierarchical paths component
- the [DataPath](/dev-master/components/data-path/) object to work with the Data URIs path
