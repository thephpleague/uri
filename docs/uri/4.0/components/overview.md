---
layout: default
title: URI Components and Parts
redirect_from:
    - /4.0/components/overview/
    - /4.0/components/
---

# URI parts and components

An URI string is composed of 8 components and 5 parts:

~~~
 foo://example.com:8042/over/there?name=ferret#nose
 \_/   \______________/\_________/ \_________/ \__/
  |           |            |            |        |
scheme     authority       path        query   fragment
  |   _____________________|__
 / \ /                        \
 urn:example:animal:ferret:nose
~~~

The URI authority part in itself can be composed of up to 3 parts.

~~~
john:doe@example.com:8042
\______/ \_________/ \__/
    |         |        |
userinfo    host     port
~~~

The userinfo part is composed of the `user` and the `pass` components.

~~~
captain:future
\_____/ \____/
   |      |
  user   pass
~~~

The `League\Uri` package uses two interfaces as fundation to implement any URI component or part.

## URI part interface

~~~php
<?php

namespace League\Uri\Interfaces;

interface UriPart
{
    //methods
    public function __toString(void);
    public function getUriComponent(void);
    public function sameValueAs(UriPart $component);
}
~~~

The `UriPart` interface exposes methods that allow a basic representation of an URI part.

### UriPart::__toString

Returns the normalized and encoded string version of the URI part. This is the form used when echoing the URI component from the URI object getter methods.

~~~php
<?php

public UriPart::__toString(void): string
~~~

#### Example

~~~php
<?php

use League\Uri\Components\Scheme;
use League\Uri\Components\UserInfo;
use League\Uri\Components\HierarchicalPath;

$scheme = new Scheme('http');
echo $scheme->__toString(); //displays 'http'

$userinfo = new UserInfo('john');
echo $userinfo->__toString(); //displays 'john'

$path = new HierarchicalPath('/toto le heros/file.xml');
echo $path->__toString(); //displays '/toto%20le%20heros/file.xml'
~~~

<p class="message-notice">Normalization and encoding are specific to the URI part.</p>

### UriPart::getUriComponent

Returns the string representation of the normalized and encoded URI part with its optional delimiter if required. This is the form used by the URI object `__toString` method when building the URI string representation.

~~~php
<?php

public UriPart::getUriComponent(void): string
~~~

#### Example

~~~php
<?php

use League\Uri\Components\Scheme;

$scheme = new Scheme('HtTp');
echo $scheme->getUriComponent(); //display 'http:'

$userinfo = new UserInfo('john');
echo $userinfo->getUriComponent(); //displays 'john@'
~~~

<p class="message-notice">Normalization, encoding and delimiters are specific to the URI part.</p>

### UriPart::getContent

<p class="message-notice">New in <code>version 4.2</code></p>

Returns the normalized and encoded version of the URI part.

~~~php
<?php

public UriPart::getContent(void): mixed
~~~

This method return type is:

* `null` : If the URI part is not defined;
* `string` : When the part is defined. This string is normalized and encoded according to the URI part rules;
* `int` : When the defined Uri component is the Uri port;

#### Example

~~~php
<?php

use League\Uri\Components\Query;
use League\Uri\Components\Port;

$component = new Query();
echo $component->getContent(); //displays null

$component = new Query('');
echo $component->getContent(); //displays ''

$component = new Port(23);
echo $component->getContent(); //displays (int) 23;
~~~

#### Notes

<p class="message-notice">To avoid BC Break <code>getContent</code> is not part of the <code>UriPart</code> interface but will be added in the next major release.</p>

### Differences between UriPart representations

To understand the differences between the described representations see the examples below:

~~~php
<?php

use League\Uri\Components\Fragment;

$component = new Fragment('');
$component->getContent(); //returns ''
echo $component; //displays ''
echo $component->getUriComponent(); //displays '#'

$altComponent = new Fragment(null);
$altComponent->getContent(); //returns null
echo $component; //displays ''
echo $altComponent->getUriComponent(); //displays ''
~~~

In both cases, the `__toString` returns the same value **but** the other methods do not.

<p class="message-notice">The <code>__toString</code> method is unabled to distinguish between an empty and an undefined URI part.</p>

### UriPart::sameValueAs

<p class="message-warning">Since <code>version 4.2</code> this method is deprecated.</p>

Compares two `UriPart` object to determine whether they are equal or not. The comparison is based on the result of `UriPart::getUriComponent` from both objects.

~~~php
<?php

public UriPart::sameValueAs(UriPart $component): bool
~~~

#### Example

~~~php
<?php

use League\Uri\Components\Host;
use League\Uri\Components\Fragment;
use League\Uri\Schemes\Http as HttpUri;

$host     = new Host('www.ExAmPLE.com');
$alt_host = new Host('www.example.com');
$fragment = new Fragment('www.example.com');
$uri      = HttpUri::createFromString('www.example.com');

$host->sameValueAs($alt_host); //return true;
$host->sameValueAs($fragment); //return false;
$host->sameValueAs($uri);
//a PHP Fatal Error is issue or a PHP7+ TypeError is thrown
~~~

<p class="message-warning">Only Uri parts objects can be compared, any other object will result in a PHP Fatal Error or a PHP7+ TypeError will be thrown.</p>

### UriPart implementing classes

* The `League\Uri\Components\UserInfo` handles [the user information part](/4.0/components/userinfo/);

## URI component interface

<p class="message-info">This interface which extends the <code>UriPart</code> interface is only implemented by URI components classes.</p>

~~~php
<?php

namespace League\Uri\Interfaces;

use League\Uri\Interfaces\UriPart;

interface Component extends UriPart
{
    public function modify($value);
}
~~~

### Component::modify

Creates a new `Component` object with a modified content. The original object is not modified.

~~~php
<?php

public Component::modify($value): Component
~~~

#### Example

~~~php
<?php

use League\Uri\Components\Query;

$query = new Query('q=url&site=thephpleague');
$new_query = $query->modify('q=yolo');
echo $new_query; //displays 'q=yolo'
echo $query;     //display 'q=url&site=thephpleague'
~~~

## Debugging

<p class="message-notice">New in <code>version 4.2</code></p>

### __debugInfo

All objects implements PHP5.6+ `__debugInfo` magic method in order to help developpers debug their code. The method is called by `var_dump` and displays the Uri components string representation.

~~~php
<?php

use League\Uri\Schemes\Host;

$host = new Host("uri.thephpleague.com");

var_dump($host);
//displays something like
// object(League\Uri\Components\Host)#1 (1) {
//     ["host"]=> string(11) "uri.thephpleague.com"
// }
~~~~~~

### __set_state

For the same purpose of debugging and object exportations PHP's magic method `__set_state` is also supported

~~~php
<?php

use League\Uri\Schemes\Host;

$host = new Host("uri.thephpleague.com");
$newHost = eval('return '.var_export($host, true).';');

$host->__toString() == $newHost->__toString();
~~~~~~

### Component implementing classes

* The `League\Uri\Components\Scheme` handles [the scheme component](/uri/4.0/components/scheme/);
* The `League\Uri\Components\User` handles [the user component](/uri/4.0/components/user/);
* The `League\Uri\Components\Pass` handles [the pass component](/uri/4.0/components/pass/);
* The `League\Uri\Components\Host` handles [the host component](/uri/4.0/components/host/);
* The `League\Uri\Components\Port` handles [the port component](/uri/4.0/components/port/);
* The `League\Uri\Components\Path` handles [the generic path component](/uri/4.0/components/path/);
* The `League\Uri\Components\HierarchicalPath` handles [the hierarchical path component](/uri/4.0/components/hierarchical-path/);
* The `League\Uri\Components\DataPath` handles [the data path component](/uri/4.0/components/datauri-path/);
* The `League\Uri\Components\Query` handles [the query component](/uri/4.0/components/query/);
* The `League\Uri\Components\Fragment` handles [the fragment component](/uri/4.0/components/fragment/);
