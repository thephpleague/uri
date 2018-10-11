---
layout: default
title: Generic URIs
redirect_from:
    - /5.0/uri/schemes/uri/
---

# Generic URIs

<p class="message-warning">Starting with version <code>1.1.0</code> all URI objects are defined in the <code>League\Uri</code> namespace. The <code>League\Uri\Schemes</code> namespace is deprecated and will be removed in the next major release.</p>

<p class="message-notice">available since version <code>1.1.0</code></p>

A generic URI object `League\Uri\Uri` is introduced to represent any `RFC3986` compatible URI. This URI object wil only validate RFC3986 rules so depending on the URI scheme the returned URI may not be valid.

~~~php
<?php

use League\Uri\Uri;
use League\Uri\Ws;

$ws_uri = 'wss://thephpleague.com/path/to?here#content';

$uri = Uri::createFromString($ws_uri);
//this will not throw an error because this URI satified RFC3986 rules
$uribis = Ws::createFromString($ws_uri);
//this will throw an exception because the URI contains a fragment which is forbidden
//for websocket URI
~~~
