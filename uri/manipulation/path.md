---
layout: default
title: URI Modifiers which affect the URI Path component
---

# Path component modifiers

## Modifying URI path

### Removing dot segments

Removes dot segments according to RFC3986:

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\RemoveDotSegments;

$uri = HttpUri::createFromString("http://www.example.com/path/../to/the/./sky/");
$modifier = new RemoveDotSegments();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/to/the/sky/"
~~~

### Removing empty segments

Removes adjacent separators with empty segment.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\RemoveEmptySegments;

$uri = HttpUri::createFromString("http://www.example.com/path//to/the//sky/");
$modifier = new RemoveEmptySegments();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky/"
~~~

### Removing trailing slash

Removes the path trailing slash if present

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\RemoveTrailingSlash;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveTrailingSlash();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky"
~~~

### Adding trailing slash

Adds the path trailing slash if not present

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\AddTrailingSlash;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new AddTrailingSlash();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky"
~~~

### Removing leading slash

Removes the path leading slash if present.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\RemoveLeadingSlash;

$uri = HttpUri::createFromString("/path/to/the/sky/");
$modifier = new RemoveLeadingSlash();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "path/to/the/sky"
~~~

### Adding leading slash

Adds the path leading slash if not present.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\AddLeadingSlash;

$uri = HttpUri::createFromString("path/to/the/sky/");
$modifier = new AddLeadingSlash();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "/path/to/the/sky"
~~~

## Modifying URI path segments

### Appending segments

Appends a segment or a path to the current URI path.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\AppendSegment;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new AppendSegment("and/above");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sky/and/above"
~~~

### Prepending segments

Prepends a segment or a path to the current URI path.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\PrependSegment;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new PrependSegment("and/above");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/and/above/path/to/the/sky/and/above"
~~~

### Replacing segments

Replaces a segment from the current URI path with a new segment or path.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\ReplaceSegment;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceSegment(3, "sea");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sea"
~~~

### Updating the modifier parameters

With the following URI modifiers:

- `AppendSegment`
- `PrependSegment`
- `ReplaceSegment`

You can update the segment string using the `withSegment` method.
This method expect a valid segment/path and will return a new instance with the updated segment.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\ReplaceSegment;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceSegment(3, "sea");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sea/"
$altModifier = $modifier->withSegment('sun');
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://www.example.com/path/to/the/sun/"
~~~

In case of the `ReplaceSegment` modifier, the offset can also be modified.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\ReplaceSegment;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceSegment(3, "sea");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/to/the/sea/"
$altModifier = $modifier->withSegment('sun')->withOffset(0);
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://www.example.com/sun/to/the/sky/"
~~~

### Updating path extension

Adds, update and or remove the path extension from the current URI path.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\Extension;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky");
$modifier = new Extension("csv");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/and/above/path/to/the/sky.csv"
~~~

You can update the extension chosen by using the `withExtension` method

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\Extension;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky");
$modifier = new Extension("csv");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/and/above/path/to/the/sky.csv"
$altModifier = $modifier->withExtension("php");
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://www.example.com/and/above/path/to/the/sky.php"
~~~

### Removing selected segments

Removes selected segments from the current URI path.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\RemoveSegments;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveSegments([1,3]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/the/and/above"
~~~

You can update the offsets chosen by using the `withKeys` method

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\RemoveSegments;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveSegments([1,3]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com/path/the/and/above/"
$altModifier = $modifier->withKeys([0,2]);
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://www.example.com/to/sky/"
~~~

### Filtering selected segments

Filter selected segments from the current URI path to keep.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\FilterSegments;
use League\Uri\Interfaces\Collection;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new FilterSegments(function ($value) {
    return $value > 0 && $value < 2;
}, Collection::FILTER_USE_KEY);
echo $newUri; //display "http://www.example.com/to/"
~~~

You can update the URI modifier using:

- `withCallable` method to alter the filtering function
- `withFlag` method to alter the filtering flag. depending on which parameter you want to use to filter the path you can use:
	- the `Collection::FILTER_USE_KEY` to filter against the segment offset;
	- the `Collection::FILTER_USE_VALUE` to filter against the segment value;
	- the `Collection::FILTER_USE_BOTH` to filter against the segment value and offset;

If no flag is used, by default the `Collection::FILTER_USE_VALUE` flag is used.
If you are using PHP 5.6+ you can directly use PHP's `array_filter` constant:

- `ARRAY_FILTER_USE_KEY` in place of `Collection::FILTER_USE_KEY`
- `ARRAY_FILTER_USE_BOTH` in place of `Collection::FILTER_USE_BOTH`

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\FilterSegments;
use League\Uri\Interfaces\Collection;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
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

## Ftp Uri Modifiers


### Add, Update, Remove the FTP typecode information

The FTP typecode information can be modified using the `Typecode` URI modifier. This methods returns a new URI object with the modified typecode. With this URI modifier you can:

- suffix the path with a new typecode

~~~php
use League\Uri\Schemes\Ftp as FtpUri;
use League\Uri\Modifiers\Typecode;

$uri = FtpUri::createFromString('ftp://thephpleague.com/path/to/image.png');
$modifier = new Typecode(FtpUri::FTP_TYPE_BINARY);
$newUri = $modifier($uri);
echo $newUri; //display 'ftp://thephpleague.com/path/to/image.png;type=i'
~~~

- update the already present typecode

~~~php
use League\Uri\Schemes\Ftp as FtpUri;
use League\Uri\Modifiers\Typecode;

$uri = FtpUri::createFromString('ftp://thephpleague.com/path/to/image.png;type=a');
$modifier = new Typecode(FtpUri::FTP_TYPE_DIRECTORY);
$newUri = $modifier($uri);
echo $newUri; //display 'ftp://thephpleague.com/path/to/image.png;type=d'
~~~

- remove the current typecode by providing an empty string.

~~~php
use League\Uri\Schemes\Ftp as FtpUri;
use League\Uri\Modifiers\Typecode;

$uri = FtpUri::createFromString('ftp://thephpleague.com/path/to/image.png;type=d');
$modifier = new Typecode(FtpUri::FTP_TYPE_EMPTY);
$newUri = $modifier($uri);
echo $newUri; //display 'ftp://thephpleague.com/path/to/image.png'
~~~

Just like others modifier it is possible to update the modifier typecode settings using the `Typecode::withTypecode` method.

<p class="message-warning">When modifying the typecode the class only validate the return string. Additional check should be done to ensure that the path is valid for a given typecode.</p>


## Data Uri Modifiers

### Update Data URI parameters

Removes selected segments from the current URI path.

~~~php
use League\Uri\Schemes\Data as DataUri;
use League\Uri\Modifiers\DataUriParameters;

$uri = DataUri::createFromString("data:text/plain;charset=US-ASCII,Hello%20World!");
$modifier = new DataUriParameters("charset=utf-8");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "data:text/plain;charset=utf-8,Hello%20World!"
~~~

You can update the offsets chosen by using the `withParameters` method

~~~php
use League\Uri\Schemes\Data as DataUri;
use League\Uri\Modifiers\DataUriParameters;

$uri = DataUri::createFromString("data:text/plain;charset=US-ASCII,Hello%20World!");
$modifier = new DataUriParameters("charset=utf-8");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "data:text/plain;charset=utf-8,Hello%20World!"
$altModifier = $modifier->withParameters("charset=utf-16;foo=bar");
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "data:text/plain;charset=utf-16;foo=bar,Hello%20World!"
~~~


### Transcoding Data URI from ASCII to Binary

Transcoding a data URI path from text to its base64 encoded version

~~~php
use League\Uri\Schemes\Data as DataUri;
use League\Uri\Modifiers\DataUriToBinary;

$uri = DataUri::createFromString("data:text/plain;charset=US-ASCII,Hello%20World!");
$modifier = new DataUriToBinary();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "data:text/plain;charset=US-ASCII;base64,SGVsbG8gV29ybGQh"
~~~

### Transcoding Data URI from Binary to ascii

Transcoding a data URI path from text to its base64 encoded version

~~~php
use League\Uri\Schemes\Data as DataUri;
use League\Uri\Modifiers\DataUriToAscii;

$uri = DataUri::createFromString("data:text/plain;charset=US-ASCII;base64,SGVsbG8gV29ybGQh");
$modifier = new DataUriToAscii();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "data:text/plain;charset=US-ASCII,Hello%20World!"
~~~
