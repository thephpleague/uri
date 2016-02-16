---
layout: default
title: URIs instantiation
---

# URI Handling

## URI instantiation

To ease URI instantiation, and because URIs come in different forms we used named constructors to offer several ways to instantiate the object.

<div class="message-warning">

If a new instance can not be created exceptions are thrown.

<ul>
	<li><code>InvalidArgumentException</code> If one of the component is invalid;</li>
	<li><code>RuntimeException</code> If the resulting URI is invalid for the selected URI scheme</li>
</ul>

</div>

### From a string

~~~php
<?php

public static Uri::createFromString(string $uri = ''): Uri
~~~

Using the `createFromString` static method you can instantiate a new URI object from a string or from any object that implements the `__toString` method. Internally, the string will be parse using the library [internal URI parser](/services/parser-uri/).

~~~php
<?php

use League\Uri\Schemes\Ftp as FtpUri;

$uri = FtpUri::createFromString('ftp://host.example.com/path/to/image.png;type=i');
~~~

### From parse_url results

~~~php
<?php

public static Uri::createFromComponents(array $components = []): Uri
~~~

You can also instantiate a new URI object using the `createFromComponents` named constructor by giving it the result of PHP's function `parse_url` or the library [internal URI parser](/services/parser-uri/).

~~~php
<?php

use League\Uri\Schemes\Ws as WsUri;

$components = parse_url('wss://foo.example.com/path/to/index.php?param=value');

$uri = WsUri::createFromComponents($components);
~~~

<div class="message-notice">
It is not recommend to instantiate an URI object using the default constructor. It is easier to always use the documentated named constructors since each URI object requires a specific set of URI components objects.
</div>

## Generic URI Handling

Out of the box the library provides the following specialized classes:

- `League\Uri\Schemes\Data` which deals with [Data URIs](/uri/datauri/);
- `League\Uri\Schemes\Ftp` which deals with the [FTP URIs](/uri/ftp/);
- `League\Uri\Schemes\Http` which deals with [HTTP and HTTPS URIs](/uri/http/);
- `League\Uri\Schemes\Ws` which deals with [WS and WSS (websocket) URIs](/uri/ws/);

<p class="message-info">But you can easily <a href="/uri/extension/">create your own class</a> to manage others scheme specific URI.</p>

## URI normalization

Out of the box the package normalizes any given URI according to the non destructive rules of RFC3986.

These non destructives rules are:

- scheme and host components are lowercased;
- query, path, fragment components are URI encoded if needed;
- the port number is removed from the URI string representation if the standard port is used;

~~~php
<?php

use League\Uri\Schemes\Http as HttpUri;

$uri = HttpUri::createFromString("hTTp://www.ExAmPLE.com:80/hello/./wor ld?who=f+3#title");
echo $uri; //displays http://www.example.com/hellow/./wor%20ld?who=f%203#title
~~~
