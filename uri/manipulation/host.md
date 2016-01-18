---
layout: default
title: URI Modifiers which affect the URI Host component
---

# Host component modifiers

## Modifying URI host

### Transcoding the host to ascii

Transcodes the host into its ascii representation according to RFC3986:

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\HostToAscii;

$uri = HttpUri::createFromString("http://스타벅스코리아.com/to/the/sky/");
$modifier = new HostToAscii();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://xn--oy2b35ckwhba574atvuzkc.com/to/the/./sky/"
~~~

### Transcoding the host to its IDN form

Transcodes the host into its idn representation according to RFC3986:

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\HostToUnicode;

$uri = HttpUri::createFromString("http://xn--oy2b35ckwhba574atvuzkc.com/to/the/./sky/");
$modifier = new HostToUnicode();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://스타벅스코리아.com/to/the/sky/"
~~~

### Removing Zone Identifier

Removes the host zone identifier if present

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\RemoveZoneIdentifier;

$uri = HttpUri::createFromString('http://[fe80::1234%25eth0-1]/path/to/the/sky.php');
$modifier = new RemoveZoneIdentifier();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display 'http://[fe80::1234]/path/to/the/sky.php'
~~~

## Modifying URI host labels

### Appending labels

Appends a label or a host to the current URI host.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\AppendLabel;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new AppendLabel("fr");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com.fr/path/to/the/sky/"
~~~

### Prepending labels

Prepends a label or a host to the current URI path.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\PrependLabel;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new PrependLabel("shop");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://shop.www.example.com/path/to/the/sky/and/above"
~~~

### Replacing labels

Replaces a label from the current URI host with a new label or a host.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\ReplaceLabel;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceSegment(2, "admin.shop");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://admin.shop.example.com/path/to/the/sky"
~~~

<p class="message-notice">The host is considered as a hierarchical component, labels are indexed from right to left according to host RFC</p>

### Updating the modifier parameters

With the following URI modifiers:

- `AppendLabel`
- `PrependLabel`
- `ReplaceLabel`

You can update the label string using the `withLabel` method.
This method expect a valid label/host and will return a new instance with the updated info.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\ReplaceLabel;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceSegment(2, "admin.shop");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://admin.shop.example.com/path/to/the/sky/"
$altModifier = $modifier->withLabel('admin');
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://admin.example.com/path/to/the/sky/"
~~~

In case of the `ReplaceLabel` modifier, the offset can also be modified.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\ReplaceSegment;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceSegment(2, "admin.shop");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://admin.shop.example.com/path/to/the/sky/"
$altModifier = $modifier->withSegment('thephpleague')->withOffset(1);
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://www.thephpleague.com/path/to/the/sky/"
~~~

### Removing selected labels

Removes selected labels from the current URI host. Labels are indicated using an array containing the labels offsets.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\RemoveLabels;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveLabels([2]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/path/the/sky/"
~~~

You can update the offsets chosen by using the `withKeys` method

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\RemoveLabels;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveLabels([2]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/path/the/sky/"
$altModifier = $modifier->withKeys([0,2]);
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://example/path/to/the/sky/"
~~~

### Filtering selected labels

Filters selected labels from the current URI path to keep. The filtering method must be a callable. You can filter the labels based on their value or on their offset.

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\FilterLabel;
use League\Uri\Interfaces\Collection;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new FilterLabel(function ($value) {
    return $value > 0 && $value < 2;
}, Collection::FILTER_USE_KEY);
echo $newUri; //display "http://example/path/to/the/sky/"
~~~

You can update the URI modifier using:

- `withCallable` method to alter the filtering callable
- `withFlag` method to alter the filtering flag. Depending on which parameter you want to use to filter the host you can use:
	- the `Collection::FILTER_USE_KEY` to filter against the label offset;
	- the `Collection::FILTER_USE_VALUE` to filter against the label value;
	- the `Collection::FILTER_USE_BOTH` to filter against the label value and offset;

If no flag is used, by default the `Collection::FILTER_USE_VALUE` flag is used.
If you are using PHP 5.6+ you can directly use PHP's `array_filter` constant:

- `ARRAY_FILTER_USE_KEY` in place of `Collection::FILTER_USE_KEY`
- `ARRAY_FILTER_USE_BOTH` in place of `Collection::FILTER_USE_BOTH`

~~~php
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\FilterLabel;
use League\Uri\Interfaces\Collection;

$uri = HttpUri::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new FilterLabel(function ($value) {
    return $value > 0 && $value < 2;
}, Collection::FILTER_USE_KEY);
echo $newUri; //display "http://example/path/to/the/sky/"
$altModifier = $modifier->withCallable(function ($value) {
    return false !== strpos($value, 'm');
})->withFlag(Collection::FILTER_USE_VALUE');
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://example.com/path/to/the/sky/"
~~~