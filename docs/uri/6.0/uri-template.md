---
layout: default
title: URI template
---

URI Template
=======

<p class="message-notice"><code>League\Uri\UriTemplate</code> is available since version <code>6.1.0</code></p>

The `League\Uri\UriTemplate` class enables expanding a URI object based on a URI template and its submitted parameters 
following [RFC 6570 URI Template](http://tools.ietf.org/html/rfc6570).

## The parser is RFC6570 compliant

Don't feel like reading RFC 6570? Here are a quick refresher to understand its rules:

- template are made of expressions consisting of an **operator** and a **variable specification**;
- a **variable specification** consists at least of one **variable name** and an optional **modifier**;
- the RFC defines 4 levels of interpolation and all 4 are supported by the given class.

The `UriTemplate::expand` public method provides the mean for expanding a URI template to generate a valid URI conforming to RFC3986 if given a supplied set of data.

~~~php
<?php

use League\Uri\UriTemplate;

$template = 'https://example.com/hotels/{hotel}/bookings/{booking}';
$params = ['booking' => '42', 'hotel' => 'Rest & Relax'];

$uriTemplate = new UriTemplate($template);
$uri = $uriTemplate->expand($params);
// $uri is an League\Uri\Uri instance.

echo $uri, PHP_EOL;
// https://example.com/hotels/Rest%20%26%20Relax/bookings/42
~~~

## Variables

<p class="message-notice">For maximum interoperability you should make sure your variables are strings or objects that expose 
the <code>__toString</code> method otherwise the value will be cast to string following PHP rules except 
for boolean values <code>true</code> and <code>false</code> which will be converted to <code>1</code> and 
<code>0</code> respectively.</p>

### Default variables can be set using the constructor

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

### Applying variables with the expand method

The default variables are overwritten by those supplied to the `expand` method.

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

$uriTemplate = new UriTemplate($template, ['version' => '1.1']);
echo $uriTemplate->expand($params), PHP_EOL;
// https://api.twitter.com/2.0/search/j/john/?q=a&q=b&limit=10
~~~

### Updating the default variables

At any given time you may update your default variables but since the `UriTemplate` is an immutable object instead
of modifying the current instance, a new instance with the modified default variables will be returned.

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

$uriTemplate = new UriTemplate($template, ['version' => '1.0', 'foo' => 'bar']);
$uriTemplate->getDefaultVariables(); //returns ['version' => '1.0']
$newUriTemplate = $uriTemplate->withDefaultVariables(['version' => '1.1']);
$newUriTemplate->getDefaultVariables(); //returns  ['version' => '1.1']
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
// will throw a League\Uri\Exceptions\TemplateCanNotBeExpanded when trying to expand the `period` value.
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
// throw a League\Uri\Exceptions\TemplateCanNotBeExpanded because the term variable is a list and not a string.
~~~

## Expressions

### Using braces in your template

The following implementation disallow the use of braces `{` or  `}` outside of being URI template expression delimiters.
If found outside of an expression an exception will be triggered. 

~~~php
<?php

use League\Uri\UriTemplate;

$template = 'https://example.com/hotels/{/book{?query*}';
$uriTemplate = new UriTemplate($template);
// will throw a League\Uri\Exceptions\SyntaxError on instantiation
~~~

If your template do require them you should URL encode them.

~~~php
<?php

use League\Uri\UriTemplate;

$template = 'https://example.com/hotels/%7B/{hotel}';
$params = ['booking' => 42, 'hotel' => 'Rest & Relax'];

$uriTemplate = new UriTemplate($template);
echo $uriTemplate->expand($params), PHP_EOL;
// https://example.com/hotels/%7B/Rest%20%26%20Relax
~~~
