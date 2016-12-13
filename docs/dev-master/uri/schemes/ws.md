---
layout: default
title: Websocket URIs
---

# Websockets URI

To work with websockets URIs you can use the `League\Uri\Schemes\Ws` class.
This class handles secure and non secure websockets URI.

## Validation

The scheme of a Websocket URI must be equal to `ws`, `wss` or be undefined. It can not contain a fragment component as per [RFC6455](https://tools.ietf.org/html/rfc6455#section-3).

<p class="message-notice">Adding contents to the fragment component throws an <code>InvalidArgumentException</code> exception</p>

~~~php
<?php

use League\Uri\Schemes\Ws as WsUri;

$uri = WsUri::createFromString('wss://thephpleague.com/path/to?here#content');
//throw an InvalidArgumentException - a fragment component was given
~~~

Apart from the fragment, the websockets URIs share the same [host validation limitation](/dev-master/uri/schemes/http/#validation) as Http URIs.