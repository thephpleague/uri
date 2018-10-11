---
layout: default
title: URI Modifiers which affect the URI Host component
redirect_from:
    - /4.0/uri/manipulation/host/
---

# Host modifiers

Here's the documentation for the included URI modifiers which are modifying the URI host component.

## Transcoding the host to ascii

Transcodes the host into its ascii representation according to RFC3986:

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\HostToAscii;

$uri = Http::createFromString("http://스타벅스코리아.com/to/the/sky/");
$modifier = new HostToAscii();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://xn--oy2b35ckwhba574atvuzkc.com/to/the/sky/"
~~~

## Transcoding the host to its IDN form

Transcodes the host into its idn representation according to RFC3986:

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\HostToUnicode;

$uriString = "http://xn--oy2b35ckwhba574atvuzkc.com/to/the/./sky/";
$uri = Http::createFromString($uriString);
$modifier = new HostToUnicode();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://스타벅스코리아.com/to/the/sky/"
~~~

## Removing Zone Identifier

Removes the host zone identifier if present

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveZoneIdentifier;

$uriString = 'http://[fe80::1234%25eth0-1]/path/to/the/sky.php';
$uri = Http::createFromString($uriString);
$modifier = new RemoveZoneIdentifier();
$newUri = $modifier->__invoke($uri);
echo $newUri; //display 'http://[fe80::1234]/path/to/the/sky.php'
~~~

## Appending labels

### Description

~~~php
<?php

public AppendLabel::__construct(string $label)
~~~

Appends a label or a host to the current URI host.

### Parameters

`$label` must be a string

### Examples

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\AppendLabel;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new AppendLabel("fr");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://www.example.com.fr/path/to/the/sky/"
~~~

## Prepending labels

### Description

~~~php
<?php

public PrependLabel::__construct(string $label)
~~~

Prepends a label or a host to the current URI path.

### Parameters

`$label` must be a string

### Examples

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\PrependLabel;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new PrependLabel("shop");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://shop.www.example.com/path/to/the/sky/and/above"
~~~

## Replacing labels

### Description

~~~php
<?php

public ReplaceLabel::__construct(int $offset, string $label)
~~~

Replaces a label from the current URI host with a new label or a host.

### Parameters

- `$label` must be a string;
- `$offset` must be a valid positive integer or `0`;

<p class="message-notice">The host is considered as a hierarchical component, labels are indexed from right to left according to host RFC</p>

### Examples

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\ReplaceLabel;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceLabel(2, "admin.shop");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://admin.shop.example.com/path/to/the/sky"
~~~

## Updating the modifier parameters

<p class="message-warning">The <code>withLabel</code> and <code>withOffset</code> methods are deprecated since <code>version 4.1</code> and will be removed in the next major release.</p>

With the following URI modifiers:

- `AppendLabel`
- `PrependLabel`
- `ReplaceLabel`

You can update the label string using the `withLabel` method.
This method expects a valid label/host and will return a new instance with the updated info.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\ReplaceLabel;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceLabel(2, "admin.shop");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://admin.shop.example.com/path/to/the/sky/"
$altModifier = $modifier->withLabel('admin');
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://admin.example.com/path/to/the/sky/"
~~~

In case of the `ReplaceLabel` modifier, the offset can also be modified.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\ReplaceLabel;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new ReplaceLabel(2, "admin.shop");
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://admin.shop.example.com/path/to/the/sky/"
$altModifier = $modifier->withLabel('thephpleague')->withOffset(1);
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://www.thephpleague.com/path/to/the/sky/"
~~~

## Removing selected labels

### Description

~~~php
<?php

public RemoveLabels::__construct(array $keys = [])
~~~

Removes selected labels from the current URI host. Labels are indicated using an array containing the labels offsets.

<p class="message-notice">The host is considered as a hierarchical component, labels are indexed from right to left according to host RFC</p>

### Examples

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveLabels;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveLabels([2]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/path/the/sky/"
~~~

<p class="message-warning">The <code>withKeys</code> method is deprecated since <code>version 4.1</code>  and will be removed in the next major release</p>

You can update the offsets chosen by using the `withKeys` method

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\RemoveLabels;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new RemoveLabels([2]);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example.com/path/the/sky/"
$altModifier = $modifier->withKeys([0,2]);
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://example/path/to/the/sky/"
~~~

## Filtering selected labels

### Description

~~~php
<?php

public FilterLabels::__construct(callable $callable, int $flag = 0)
~~~

Filter the labels from the current URI host to keep.

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

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\FilterLabels;
use League\Uri\Interfaces\Collection;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new FilterLabels(function ($value) {
    return $value > 0 && $value < 2;
}, Collection::FILTER_USE_KEY);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example/path/to/the/sky/"
~~~

### Methods

<p class="message-warning">The <code>withCallable</code> and <code>withFlag</code> methods are deprecated since <code>version 4.1</code> and will be removed in the next major release</p>

You can update the URI modifier using:

- `withCallable` method to alter the filtering callable
- `withFlag` method to alter the filtering flag.

~~~php
<?php

use League\Uri\Schemes\Http;
use League\Uri\Modifiers\FilterLabels;
use League\Uri\Interfaces\Collection;

$uri = Http::createFromString("http://www.example.com/path/to/the/sky/");
$modifier = new FilterLabels(function ($value) {
    return $value > 0 && $value < 2;
}, Collection::FILTER_USE_KEY);
$newUri = $modifier->__invoke($uri);
echo $newUri; //display "http://example/path/to/the/sky/"

$altModifier = $modifier->withCallable(function ($value) {
    return false !== strpos($value, 'm');
})->withFlag(Collection::FILTER_USE_VALUE);
$altUri = $altModifier->__invoke($uri);
echo $altUri; //display "http://example.com/path/to/the/sky/"
~~~