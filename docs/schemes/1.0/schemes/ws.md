---
layout: default
title: Websocket URIs
redirect_from:
    - /5.0/uri/schemes/ws/
---

# Websockets URI

<p class="message-warning">Starting with version <code>1.1.0</code> all URI objects are defined in the <code>League\Uri</code> namespace. The <code>League\Uri\Schemes</code> namespace is deprecated and will be removed in the next major release.</p>

To work with websockets URIs you can use the `League\Uri\Ws` class. This class handles secure and non secure websockets URI.

## Validation

The scheme of a Websocket URI must be equal to `ws`, `wss` or be undefined. It can not contain a fragment component as per [RFC6455](https://tools.ietf.org/html/rfc6455#section-3).

<p class="message-notice">Adding contents to the fragment component throws an <code>UriException</code> exception</p>

~~~php
<?php

use League\Uri;

$uri = Uri\Ws::createFromString('wss://thephpleague.com/path/to?here#content');
// will throw an League\Uri\UriException
~~~

Apart from the fragment and the scheme definition, the websockets URIs share the same [validation rules](/5.0/uri/schemes/http/#validation) as Http URIs.