---
layout: default
title: URI formatter
---

URI Formatter
=======

The Formatter class helps you format your URI according to your output.

## API

~~~php
<?php

public Formatter::setEncoding(string $format): void
public Formatter::setQuerySeparator(string $separator): void
public Formatter::preserveQuery(bool $status): void
public Formatter::preserveFragment(bool $status): void
public Formatter::__invoke(mixed $uri): string
~~~

This main method `__invoke` expects an object implementing one of the following interface:

- The PSR-7 `UriInterface`;
- The `League\Interfaces\Uri` interface;
- The `League\Uri\Components\ComponentInterface` Interface;

and returns a string representation of the submitted object according to the settings you gave it using the remaining methods.

<p class="message-notice">The returned string <strong>MAY</strong> no longer be a valid URI</p>

### setEncoding

By default the formatter encode each URI component using RFC3986 rules. You can change the component encoding algorithm by specifying one of the predefined constant:

- `Formatter::RFC3986_ENCODING` to encode the URI and its component according to RFC3986;
- `Formatter::RFC3987_ENCODING` to encode the URI and its component according to RFC3987;
- `Formatter::NO_ENCODING` to remove any encoding from each URI component;

### setQuerySeparator

If you want to generate an URI string compatible with HTML rules you need for instance to convert the `&` to its HTML entity `&amp;`. This setter enables you to change the query separator in the return URI string.

### preserveQuery and preserveFragment

By default PSR-7 `UriInterface` does not preserve query and fragment delimiters in the string representation. If you want to keep them you need to specify this behaviour to the `Formatter` object. By default, the `Formatter` does not keep them too.

## Example

~~~php
<?php

use League\Uri\Formatter;
use Zend\Diactoros\Uri as DiactorosUri;

$formatter = new Formatter();
$formatter->setHostEncoding(Formatter::RFC3987);
$formatter->setQuerySeparator('&amp;');
$formatter->preserveFragment(true);

$uri = new DiactorosUri('https://xn--p1ai.ru:81?foo=ba%20r&baz=bar');
echo $formatter($uri);
//displays 'https://рф.ru:81?foo=ba r&amp;baz=bar#'
~~~