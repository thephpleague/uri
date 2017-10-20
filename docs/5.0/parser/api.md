---
layout: default
title: URI Parser Public API
---

Public API
=======

## URI parsing

<p class="message-notice"><code>Uri\parse</code> is available since version <code>1.1.0</code></p>

URI parsing can be done:

- using the `Uri\Parser::__invoke` method
- the helper function `Uri\parse` which is an alias for `Uri\Parser::__invoke` method

Learn more about the differences with PHP's `parse_url` in the [parser](/5.0/parser/parser#uri-parsing) section.

~~~php
<?php

use League\Uri;

$parser = new Uri\Parser();
var_export($parser('http://foo.com?@bar.com/'));
//returns the following array
//array(
//  'scheme' => 'http',
//  'user' => null,
//  'pass' => null,
//  'host' => 'foo.com',
//  'port' => null,
//  'path' => '',
//  'query' => '@bar.com/',
//  'fragment' => null,
//);

var_export(Uri\parse('http://foo.com?@bar.com/'));
//returns the following array
//array(
//  'scheme' => 'http',
//  'user' => null,
//  'pass' => null,
//  'host' => 'foo.com',
//  'port' => null,
//  'path' => '',
//  'query' => '@bar.com/',
//  'fragment' => null,
//);
~~~

## URI building

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

## Scheme validation

<p class="message-notice">available since version <code>1.2.0</code></p>

You can validate any scheme component using:

- the `Uri\Parser::isScheme` method;
- the helper function `Uri\is_scheme` which is an alias for `Uri\Parser::isScheme` method.

~~~php
<?php

use League\Uri;

$parser = new Uri\Parser();
$parser->isScheme('example.com'); //returns false
$parser->isScheme('ssh+svn'); //returns true

Uri\is_scheme('data');  //returns true
Uri\is_scheme('data:'); //returns false
~~~

## Host validation

<p class="message-notice"><code>Uri\is_host</code> is available since version <code>1.1.0</code></p>

You can validate any host component using:

- the `Uri\Parser::isHost` method;
- the helper function `Uri\is_host` which is an alias for `Uri\Parser::isHost` method.

Learn more about host validation in the [parser](/5.0/parser/parser#host-validation) section.

~~~php
<?php

use League\Uri;

$parser = new Uri\Parser();
$parser->isHost('example.com'); //returns true
$parser->isHost('/path/to/yes'); //returns false

Uri\is_host('[:]'); //returns true
Uri\is_host('[127.0.0.1]'); //returns false
~~~

## Port validation

<p class="message-notice">available since version <code>1.2.0</code></p>

You can validate any port component using:

- the `Uri\Parser::isPort` method;
- the helper function `Uri\is_port` which is an alias for `Uri\Parser::isPort` method.

~~~php
<?php

use League\Uri;

$parser = new Uri\Parser();
$parser->isPort('example.com'); //returns false
$parser->isPort(888);           //returns true

Uri\is_port('23');    //returns true
Uri\is_port('data:'); //returns false
~~~