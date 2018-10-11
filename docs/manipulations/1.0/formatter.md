---
layout: default
title: URI formatter
redirect_from:
    - /5.0/manipulations/formatter/
---

URI Formatter
=======

The Formatter class helps you format your URI according to your output.

## API

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
$formatter->setHostEncoding(Formatter::RFC3987_ENCODING);
$formatter->setQuerySeparator('&amp;');
$formatter->preserveFragment(true);

$uri = new DiactorosUri('https://xn--p1ai.ru:81?foo=ba%20r&baz=bar');
echo $formatter($uri);
//displays 'https://рф.ru:81?foo=ba r&amp;baz=bar#'
~~~

## Function alias

<p class="message-info">available since version <code>1.1.0</code></p>

~~~php
<?php

function League\Uri\uri_to_rfc3986(mixed $uri): string
function League\Uri\uri_to_rfc3987(mixed $uri): string
~~~

These functions convert any The PSR-7 `UriInterface` or `League\Interfaces\Uri` implementing object into an URI string encoded in RFC3986 or RFC3987.  
`Uri\uri_to_rfc3986` and `Uri\uri_to_rfc3987` always preserve query and fragment presence.

~~~php
<?php

use Zend\Diactoros\Uri as DiactorosUri;
use function League\Uri\uri_to_rfc3986;
use function League\Uri\uri_to_rfc3987;

$uri = new DiactorosUri('http://xn--bb-bjab.be/toto/тестовый_путь/');

echo uri_to_rfc3986($uri);
// displays 'http://xn--bb-bjab.be/toto/%D1%82%D0%B5%D1%81%D1%82%D0%BE%D0%B2%D1%8B%D0%B9_%D0%BF%D1%83%D1%82%D1%8C/'

echo uri_to_rfc3987($uri);
// displays 'http://bébé.be/toto/тестовый_путь/',
~~~