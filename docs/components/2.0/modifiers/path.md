---
layout: default
title: Path modifiers
---

Path modifiers
=======

The following modifiers update and normalize the path component.

<p class="message-notice">Because each modification is done after parsing and building, the 
resulting path may update the component character encoding. These changes are expected because of 
the rules governing parsing and building path string.</p>

## UriModifier::removeDotSegments

Removes dot segments according to RFC3986:

~~~php
$uri = Http::createFromString("http://www.example.com/path/../to/the/./sky/");
$newUri = UriModifier::removeDotSegments($uri);

echo $newUri; //display "http://www.example.com/to/the/sky/"
~~~

## UriModifier::removeEmptySegments

Removes adjacent separators with empty segment.

~~~php
$uri = Http::createFromString("http://www.example.com/path//to/the//sky/");
$newUri = UriModifier::removeEmptySegments($uri);

echo $newUri; //display "http://www.example.com/path/to/the/sky/"
~~~

## UriModifier::removeTrailingSlash

Removes the path trailing slash if present

~~~php
$uri = Http::createFromString("http://www.example.com/path/?foo=bar");
$newUri = UriModifier::removeTrailingSlash($uri);

echo $newUri; //display "http://www.example.com/path?foo=bar"
~~~

## UriModifier::addTrailingSlash

Adds the path trailing slash if not present

~~~php
$uri = Http::createFromString("http://www.example.com/sky#top");
$newUri = UriModifier::addTrailingSlash($uri);

echo $newUri; //display "http://www.example.com/sky/#top"
~~~

## UriModifier::removeLeadingSlash

Removes the path leading slash if present.

~~~php
$uri = Http::createFromString("/path/to/the/sky/");
$newUri = UriModifier::removeLeadingSlash($uri);

echo $newUri; //display "path/to/the/sky"
~~~

## UriModifier::addLeadingSlash

Adds the path leading slash if not present.

~~~php
$uri = Http::createFromString("path/to/the/sky/");
$newUri = UriModifier::addLeadingSlash($uri);

echo $newUri; //display "/path/to/the/sky"
~~~

## UriModifier::replaceDirname

Adds, updates and or removes the path dirname from the current URI path.

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky");
$newUri = UriModifier::replaceDirname($uri, '/road/to');

echo $uri->getPath();    //display "/path/to/the/sky"
echo $newUri->getPath(); //display "/road/to/sky"
~~~

## UriModifier::replaceBasename

Adds, updates and or removes the path basename from the current URI path.

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky");
$newUri = UriModifier::replaceBasename($uri, "paradise.xml");

echo $uri->getPath();    //display "/path/to/the/sky"
echo $newUri->getPath(); //display "/path/to/the/paradise.xml"
~~~

## UriModifier::replaceExtension

Adds, updates and or removes the path extension from the current URI path.

~~~php
$uri = Http::createFromString("http://www.example.com/export.html");
$newUri = UriModifier::replaceExtension($uri, 'csv');

echo $uri->getPath();    //display "/export.html"
echo $newUri->getPath(); //display "/export.csv"
~~~

## UriModifier::addBasePath

Adds the basepath to the current URI path.

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky");
$newUri = UriModifier::addBasePath($uri, '/the/real');

echo $uri->getPath();    //display "/path/to/the/sky"
echo $newUri->getPath(); //display "/the/real/path/to/the/sky"
~~~

## UriModifier::removeBasePath

Removes the basepath from the current URI path.

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky");
$newUri = UriModifier::removeBasePath($uri, "/path/to/the");

echo $uri->getPath();    //display "/path/to/the/sky"
echo $newUri->getPath(); //display "/sky"
~~~

## UriModifier::appendSegment

Appends a path to the current URI path.

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$newUri = UriModifier::appendSegment($uri, "and/above");

echo $uri->getPath();    //display "/path/to/the/sky"
echo $newUri->getPath(); //display "/path/to/the/sky/and/above"
~~~

## UriModifier::prependSegment

Prepends a path to the current URI path.

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$newUri = UriModifier::prependSegment($uri, "and/above");

echo $uri->getPath();    //display "/path/to/the/sky"
echo $newUri->getPath(); //display "/and/above/path/to/the/sky/"
~~~

## UriModifier::replaceSegment

Replaces a segment from the current URI path with a new path.

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$newUri = UriModifier::replaceSegment($uri, 3, "sea");

echo $uri->getPath();    //display "/path/to/the/sky/"
echo $newUri->getPath(); //display "/path/to/the/sea/"
~~~

<p class="message-info">This modifier supports negative offset</p>

The previous example can be rewritten using negative offset:

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$newUri = UriModifier::replaceSegment($uri, -1, "sea");

echo $uri->getPath();    //display "/path/to/the/sky/"
echo $newUri->getPath(); //display "/path/to/the/sea/"
~~~

## UriModifier::removeSegments

Removes selected segments from the current URI path by providing the segments offset.

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$newUri = UriModifier::removeSegments($uri, 1, 3);

echo $uri->getPath();    //display "/path/to/the/sky/"
echo $newUri->getPath(); //display "/path/the/"
~~~

<p class="message-info">This modifier supports negative offset</p>

~~~php
$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$newUri = UriModifier::removeSegments($uri, -1, -2]);

echo $uri->getPath();    //display "/path/to/the/sky/"
echo $newUri->getPath(); //display "/path/the/"
~~~

## UriModifier::replaceDataUriParameters

Update Data URI parameters

~~~php
$uri = DataUri::createFromString("data:text/plain;charset=US-ASCII,Hello%20World!");
$newUri = UriModifier::replaceDataUriParameters($uri, "charset=utf-8");

echo $uri->getPath();    //display "text/plain;charset=US-ASCII,Hello%20World!"
echo $newUri->getPath(); //display "text/plain;charset=utf-8,Hello%20World!"
~~~

## UriModifier::dataPathToBinary

Converts a data URI path from text to its base64 encoded version

~~~php
$uri = DataUri::createFromString("data:text/plain;charset=US-ASCII,Hello%20World!");
$newUri = UriModifier::dataPathToBinary($uri);

echo $uri->getPath();    //display "text/plain;charset=US-ASCII,Hello%20World!"
echo $newUri->getPath(); //display "text/plain;charset=US-ASCII;base64,SGVsbG8gV29ybGQh"

~~~

## UriModifier::dataPathToAscii

Converts a data URI path from text to its base64 encoded version

~~~php
$uri = DataUri::createFromString("data:text/plain;charset=US-ASCII;base64,SGVsbG8gV29ybGQh");
$newUri = UriModifier::dataPathToAscii($uri);

echo $uri->getPath();    //display "text/plain;charset=US-ASCII;base64,SGVsbG8gV29ybGQh"
echo $newUri->getPath(); //display "text/plain;charset=US-ASCII,Hello%20World!"
~~~
