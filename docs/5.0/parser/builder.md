---
layout: default
title: URI Builder
---

URI Builder
=======

~~~php
<?php

use League\Uri;

function build(array $components): string
~~~

<p class="message-info"><code>Uri\build</code> is available since version <code>1.1.0</code></p>

You can rebuild a URI from its hash representation returned by the `Parser::__invoke` method or PHP's `parse_url` function using the helper function `Uri\build`.  

If you supply your own hash you are responsible for providing valid encoded components without their URI delimiters.

~~~php
<?php

use League\Uri;

$base_uri = 'http://hello:world@foo.com?@bar.com/';
$components = Uri\parse($base_uri);
//returns the following array
//array(
//  'scheme' => 'http',
//  'user' => 'hello',
//  'pass' => 'world',
//  'host' => 'foo.com',
//  'port' => null,
//  'path' => '',
//  'query' => '@bar.com/',
//  'fragment' => null,
//);

$uri = Uri\build($components);

echo $uri; //displays http://hello@foo.com?@bar.com/
~~~

The `Uri\build` function never output the `pass` component as suggested by [RFC3986](https://tools.ietf.org/html/rfc3986#section-7.5).