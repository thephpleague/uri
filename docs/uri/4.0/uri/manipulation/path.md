---
layout: default
title: URI Modifiers which affect the URI Path component
redirect_from:
    - /4.0/uri/manipulation/path/
---

# Path modifiers

Here's the documentation for the included URI modifiers which are modifying the URI path component.

## Removing dot segments

Removes dot segments according to RFC3986:

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveDotSegments;

$uri = Http::createFromString("http://www.example.com/path/../to/the/./sky/");
$modifier = new RemoveDotSegments();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/to/the/sky/"
~~~

## Removing empty segments

Removes adjacent separators with empty segment.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveEmptySegments;

$uri = Http::createFromString("http://www.example.com/path//to/the//sky/");
$modifier = new RemoveEmptySegments();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky/"
~~~

## Removing trailing slash

Removes the path trailing slash if present

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveTrailingSlash;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveTrailingSlash();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky"
~~~

## Adding trailing slash

Adds the path trailing slash if not present

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AddTrailingSlash;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new AddTrailingSlash();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky"
~~~

## Removing leading slash

Removes the path leading slash if present.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveLeadingSlash;

$uri = Http::createFromString("/path/to/the/sky/");
$modifier = new RemoveLeadingSlash();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "path/to/the/sky"
~~~

## Adding leading slash

Adds the path leading slash if not present.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AddLeadingSlash;

$uri = Http::createFromString("path/to/the/sky/");
$modifier = new AddLeadingSlash();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "/path/to/the/sky"
~~~

## Appending segments

### Description

~~~php
<?php

public AppendSegment::__construct(string $segment)
~~~

Appends a segment or a path to the current URI path.

### Parameters

`$segment` must be a string

### Examples

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AppendSegment;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new AppendSegment("and/above");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky/and/above"
~~~

## Prepending segments

### Description

~~~php
<?php

public PrependSegment::__construct(string $segment)
~~~

Prepends a segment or a path to the current URI path.

### Parameters

`$segment` must be a string

### Examples

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\PrependSegment;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new PrependSegment("and/above");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/and/above/path/to/the/sky/and/above"
~~~

## Replacing segments

### Description

~~~php
<?php

public ReplaceSegment::__construct(int $offset, string $segment)
~~~

Replaces a segment from the current URI path with a new segment or path.

### Parameters

- `$segment` must be a string;
- `$offset` must be a valid positive integer or `0`;

### Examples

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\ReplaceSegment;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceSegment(3, "sea");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sea"
~~~

## Updating the modifier parameters

<p class="message-warning">The <code>withSegment</code> and <code>withOffset</code> methods are deprecated since <code>version 4.1</code> and will be removed in the next major release.</p>

With the following URI modifiers:

- `AppendSegment`
- `PrependSegment`
- `ReplaceSegment`

You can update the segment string using the `withSegment` method.
This method expect a valid segment/path and will return a new instance with the updated segment.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\ReplaceSegment;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceSegment(3, "sea");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sea/"
$altModifier = $modifier->withSegment('sun');
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://www.example.com/path/to/the/sun/"
~~~

In case of the `ReplaceSegment` modifier, the offset can also be modified.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\ReplaceSegment;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceSegment(3, "sea");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sea/"
$altModifier = $modifier->withSegment('sun')->withOffset(0);
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://www.example.com/sun/to/the/sky/"
~~~

## Updating path extension

### Description

~~~php
<?php

public Extension::__construct(string $extension)
~~~

Adds, update and or remove the path extension from the current URI path.

### Parameters

`$extension` must be a valid string extension

### Examples

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\Extension;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky");
$modifier = new Extension("csv");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/and/above/path/to/the/sky.csv"
~~~

<p class="message-warning">The <code>withExtension</code> method is deprecated since <code>version 4.1</code>and will be removed in the next major release</p>

You can update the extension chosen by using the `withExtension` method

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\Extension;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky");
$modifier = new Extension("csv");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/and/above/path/to/the/sky.csv"
$altModifier = $modifier->withExtension("php");
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://www.example.com/and/above/path/to/the/sky.php"
~~~

## Removing selected segments

### Description

~~~php
<?php

public RemoveSegments::__construct(array $keys = [])
~~~

Removes selected segments from the current URI path.

### Examples

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveSegments;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveSegments([1,3]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/the/and/above"
~~~

<p class="message-warning">The <code>withKeys</code> method is deprecated since <code>version 4.1</code>and will be removed in the next major release</p>

You can update the offsets chosen by using the `withKeys` method

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveSegments;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveSegments([1,3]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/the/and/above/"
$altModifier = $modifier->withKeys([0,2]);
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://www.example.com/to/sky/"
~~~

## Filtering selected segments

### Description

~~~php
<?php

public FilterSegments::__construct(callable $callable, int $flag = 0)
~~~

Filter selected segments from the current URI path to keep.

### Parameters

- The `$callable` argument is a `callable` used by PHP's `array_filter`
- The `$flag` argument is a `int` used by PHP's `array_filter`

<p class="message-notice">
For Backward compatibility with PHP5.5 which lacks these flags constant you can use the library constants instead:</p>

<table>
<thead>
<tr><th>League\Uri\Interfaces\Collection constants</th><th>PHP's 5.6+ constants</th></tr>
</thead>
<tbody>
<tr><td><code>Collection::FILTER_USE_KEY</code></td><td><code>ARRAY_FILTER_USE_KEY</code></td></tr>
<tr><td><code>Collection::FILTER_USE_BOTH</code></td><td><code>ARRAY_FILTER_USE_BOTH</code></td></tr>
<tr><td><code>Collection::FILTER_USE_VALUE</code></td><td><code>0</code></td></tr>
</tbody>
</table>

### Examples

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\FilterSegments;
use League\Uri\Interfaces\Collection;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new FilterSegments(function ($value) {
    return $value > 0 && $value < 2;
}, Collection::FILTER_USE_KEY);
echo $newUri; //display "http://www.example.com/to/"
~~~

You can update the URI modifier using:

<p class="message-warning">The <code>withCallable</code> and <code>withFlag</code> methods are deprecated since <code>version 4.1</code> and will be removed in the next major release</p>

- `withCallable` method to alter the filtering function
- `withFlag` method to alter the filtering flag.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\FilterSegments;
use League\Uri\Interfaces\Collection;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new FilterSegments(function ($value) {
    return $value > 0 && $value < 2;
}, Collection::FILTER_USE_KEY);
echo $newUri; //display "http://www.example.com/to/"
$altModifier = $modifier->withCallable(function ($value) {
    return false !== strpos($value, 'h');
})->withFlag(Collection::FILTER_USE_VALUE');
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://www.example.com/path/the/sky/"
~~~

## Add, Update, Remove the FTP typecode information

### Description

~~~php
<?php

public Typecode::__construct(int $flag = 0)
~~~

This methods returns a new URI object with the modified typecode.

### Examples

With this URI modifier you can:

- suffix the path with a new typecode

~~~php
<?php

use League\Uri\Schemes\Ftp as FtpUri;
use League\Uri\Modifiers\Typecode;

$uri = FtpUri::createFromString('ftp://thephpleague.com/path/to/image.png');
$modifier = new Typecode(FtpUri::FTP_TYPE_BINARY);
$newUri = $modifier($uri);
echo $newUri; //display 'ftp://thephpleague.com/path/to/image.png;type=i'
~~~

- update the already present typecode

~~~php
<?php

use League\Uri\Schemes\Ftp as FtpUri;
use League\Uri\Modifiers\Typecode;

$uri = FtpUri::createFromString('ftp://thephpleague.com/path/to/image.png;type=a');
$modifier = new Typecode(FtpUri::FTP_TYPE_DIRECTORY);
$newUri = $modifier($uri);
echo $newUri; //display 'ftp://thephpleague.com/path/to/image.png;type=d'
~~~

- remove the current typecode by providing an empty string.

~~~php
<?php

use League\Uri\Schemes\Ftp as FtpUri;
use League\Uri\Modifiers\Typecode;

$uri = FtpUri::createFromString('ftp://thephpleague.com/path/to/image.png;type=d');
$modifier = new Typecode(FtpUri::FTP_TYPE_EMPTY);
$newUri = $modifier($uri);
echo $newUri; //display 'ftp://thephpleague.com/path/to/image.png'
~~~


Just like others modifier it is possible to update the modifier typecode settings using the `Typecode::withType` method.

<p class="message-warning">The <code>withType</code> method is deprecated since <code>version 4.1</code>and will be removed in the next major release</p>

<p class="message-warning">When modifying the typecode the class only validate the return string. Additional check should be done to ensure that the path is valid for a given typecode.</p>


## Update Data URI parameters

### Description

~~~php
<?php

public DataUriParameters::__construct(string $parameters = 0)
~~~

Removes selected segments from the current URI path.

### Parameters

`$parameters` is a string containing the parameters to be associated with the Data Uri.

### Examples

~~~php
<?php

use League\Uri\Schemes\Data as DataUri;
use League\Uri\Modifiers\DataUriParameters;

$uriString = "data:text/plain;charset=US-ASCII,Hello%20World!";
$uri = DataUri::createFromString($uriString);
$modifier = new DataUriParameters("charset=utf-8");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "data:text/plain;charset=utf-8,Hello%20World!"
~~~

<p class="message-warning">The <code>withParameters</code> method is deprecated since <code>version 4.1</code>and will be removed in the next major release</p>

You can update the offsets chosen by using the `withParameters` method

~~~php
<?php

use League\Uri\Schemes\Data as DataUri;
use League\Uri\Modifiers\DataUriParameters;

$uriString = "data:text/plain;charset=US-ASCII,Hello%20World!";
$uri = DataUri::createFromString($uriString);
$modifier = new DataUriParameters("charset=utf-8");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "data:text/plain;charset=utf-8,Hello%20World!"
$altModifier = $modifier->withParameters("charset=utf-16;foo=bar");
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "data:text/plain;charset=utf-16;foo=bar,Hello%20World!"
~~~

## Transcoding Data URI from ASCII to Binary

Transcoding a data URI path from text to its base64 encoded version

~~~php
<?php

use League\Uri\Schemes\Data as DataUri;
use League\Uri\Modifiers\DataUriToBinary;

$uriString = "data:text/plain;charset=US-ASCII,Hello%20World!";
$uri = DataUri::createFromString($uriString);
$modifier = new DataUriToBinary();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "data:text/plain;charset=US-ASCII;base64,SGVsbG8gV29ybGQh"
~~~

## Transcoding Data URI from Binary to ascii

Transcoding a data URI path from text to its base64 encoded version

~~~php
<?php

use League\Uri\Schemes\Data as DataUri;
use League\Uri\Modifiers\DataUriToAscii;

$uriString = "data:text/plain;charset=US-ASCII;base64,SGVsbG8gV29ybGQh";
$uri = DataUri::createFromString($uriString);
$modifier = new DataUriToAscii();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "data:text/plain;charset=US-ASCII,Hello%20World!"
~~~
