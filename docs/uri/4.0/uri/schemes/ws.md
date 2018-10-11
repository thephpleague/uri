---
layout: default
title: Websocket URIs
redirect_from:
    - /4.0/uri/schemes/ws/
---

# Websockets URI

To work with websockets URIs you can use the `League\Uri\Schemes\Ws` class.
This class handles secure and non secure websockets URI.

## Validation

Websockets URIs must contain a `ws` or the `wss` scheme. It can not contain a fragment component as per [RFC6455](https://tools.ietf.org/html/rfc6455#section-3).

<p class="message-notice">Adding contents to the fragment component throws an <code>InvalidArgumentException</code> exception</p>

~~~php
<?php

use League\Uri\Schemes\Ws as WsUri;

$uri = WsUri::createFromString('wss://thephpleague.com/path/to?here#content');
//throw an InvalidArgumentException - a fragment component was given
~~~

Apart from the fragment, the websockets URIs share the same [host validation limitation](/uri/4.0/uri/schemes/http/#validation) as Http URIs.

<p class="message-notice">Starting with version <code>4.2</code> schemeless FTP Uri will no longer trigger an <code>InvalidArgumentException</code> exception</p>

## Properties

Websockets URIs objects uses the specialized [HierarchicalPath](/uri/4.0/components/hierarchical-path/) class to represents its path. using PHP's magic `__get` method you can access the object path and get more informations about the underlying path.

~~~php
<?php

use League\Uri\Schemes\Ws as WsUri;

$uri = WsUri::createFromString('wss://thephpleague.com/path/to?here');
echo $uri->path->getBasename();  //display '/path'
echo $uri->path->getDirname();   //display 'to'
echo $uri->path->getExtension(); //display ''
$uri->path->getSegments(); //returns an array representation of the path segments
~~~