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

<p class="message-notice"><code>Uri\build</code> is available since version <code>1.1.0</code></p>

You can rebuild a URI from a array using the helper function `Uri\build`.

~~~php
<?php

use League\Uri;

$uri = Uri\build([
    'scheme' => 'http',
    'user' => null,
    'pass' => null,
    'host' => 'foo.com',
    'port' => null,
    'path' => '',
    'query' => '@bar.com/',
    'fragment' => null,
]);

echo $uri; //displays http://foo.com?@bar.com/
~~~

The `Uri\build` function never output the `pass` component as suggested by [RFC3986](https://tools.ietf.org/html/rfc3986#section-7.5).