---
layout: default
title: URI template
---

URI Template
=======

<p class="message-info"><code>League\Uri\UriTemplate</code> is available since version <code>6.1.0</code></p>

The `League\Uri\UriTemplate` class enables expanding a URI object based on a URI template and its submitted parameters 
following [RFC 6570 URI Template](http://tools.ietf.org/html/rfc6570).

### The parser is RFC6570 compliant

Don't feel like reading RFC 6570? Here are a simple refresher to understand its rules:

- template are made of expressions consisting of an **operator** and a **variable specification**;
- a **variable specification** consists at least of one **variable name** and an optional **modifier**;

The `UriTemplate::expand` public method provides the mean for expanding a URI template to generate a valid URI conforming to RFC3986 if given a supplied set of data.

~~~php
<?php

use League\Uri\UriTemplate;

$template = 'https://example.com/hotels/{hotel}/bookings/{booking}';
$params = ['booking' => 42, 'hotel' => 'Rest & Relax'];

$uriTemplate = new UriTemplate($template);
$uri = $uriTemplate->expand($params);
// $uri is an League\Uri\Uri instance.

echo $uri, PHP_EOL;
// https://example.com/hotels/Rest%20%26%20Relax/bookings/42
~~~

### Default parameters can be set using the constructor

The constructor takes a optional set of default variables that can be applied by default when expanding the URI template.

~~~php
<?php

use League\Uri\UriTemplate;
$template = 'https://api.twitter.com/{version}/search/{term:1}/{term}/{?q*,limit}';

$params = [
    'term' => 'john',
    'q' => ['a', 'b'],
    'limit' => '10',
];

$uriTemplate = new UriTemplate($template, ['version' => 1.1]);
echo $uriTemplate->expand($params), PHP_EOL;
// https://api.twitter.com/1.1/search/j/john/?q=a&q=b&limit=10
~~~

The default variables can be overwritten by those supplied to the `expand` methods.

~~~php
<?php

use League\Uri\UriTemplate;
$template = 'https://api.twitter.com/{version}/search/{term:1}/{term}/{?q*,limit}';

$params = [
    'term' => 'john',
    'q' => ['a', 'b'],
    'limit' => '10',
    'version' => '2.0'
];

$uriTemplate = new UriTemplate($template, ['version' => 1.1]);
echo $uriTemplate->expand($params), PHP_EOL;
// https://api.twitter.com/2.0/search/j/john/?q=a&q=b&limit=10
~~~

### Nested array are disallowed

<p class="message-warning">This class follows only the RFC6570 requirements and thus, does not support nested array like the one used with <code>http_build_query</code></p>

~~~php
<?php

use League\Uri\UriTemplate;

$template = 'https://example.com/hotels/{hotel}/book{?query*}';
$params = [
    'hotel' => 'Rest & Relax',
    'query' => [
        'period' => [
            'start' => '2020-01-12',
            'end' => '2020-01-15',
        ],
    ],
];

$uriTemplate = new UriTemplate($template);
$uriTemplate->expand($params);
// will throw a UriTemplateException when trying to expand the `period` value.
~~~

### Using the prefix modifier on a list will trigger an exception.

While this is not forbidden by the RFC, the `UriTemplate` class will throw an exception 
if an attempt is made to use the prefix modifier with a list of value. Other implementations
will silently ignore the modifier **but** this package will trigger the exception to alert 
the user that something might be wrong and that the generated URI might not be the one expected.

~~~php
<?php

use League\Uri\UriTemplate;
$template = 'https://api.twitter.com/{version}/search/{term:1}/{term}/{?q*,limit}';

$params = [
    'term' => ['john', 'doe'],
    'q' => ['a', 'b'],
    'limit' => '10',
    'version' => '2.0'
];

$uriTemplate = new UriTemplate($template);
echo $uriTemplate->expand($params), PHP_EOL;
// throw a UriTemplateException because the term variable is a list and not a string.
~~~
