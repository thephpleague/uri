---
layout: default
title: URI components
---

The Host
=======

The library provides a `Host` class to ease complex host manipulation. The object implements:

- the `League\Uri\Components\ComponentInterface` Interface
- PHP's `Countable` Interface
- PHP's `IteratorAggregate` Interface

## Host creation

<p class="message-warning">If the submitted value is not a valid host an <code>InvalidArgumentException</code> will be thrown.</p>

### Host::createFromLabels

A host is a collection of labels delimited by the host separator `.`. So it is possible to create a `Host` object using a collection of labels with the `Host::createFromLabels` method.

The method expects at most 2 arguments:

- The first required argument must be a collection of label (an `array` or a `Traversable` object). **The labels must be ordered hierarchically, this mean that the array should have the top-level domain in its first entry**.

- The second optional argument, a `Host` constant, tells whether this is an <abbr title="Fully Qualified Domain Name">FQDN</abbr> or not:
    - `Host::IS_ABSOLUTE` creates an a fully qualified domain name `Host` object;
    - `Host::IS_RELATIVE` creates an a partially qualified domain name `Host` object;

By default this optional argument equals to `Host::IS_RELATIVE`.

<p class="message-warning">Since an IP is not a hostname, the class will throw an <code>InvalidArgumentException</code> if you try to create an fully qualified domain name with a valid IP address.</p>

~~~php
<?php

use League\Uri\Components\Host;

$host = Host::createFromLabels(['com', 'example', 'shop']);
echo $host; //display 'shop.example.com'

$fqdn = Host::createFromLabels(['com', 'example', 'shop'], Host::IS_ABSOLUTE);
echo $fqdn; //display 'shop.example.com.'

$ip_host = Host::createFromLabels(['0.1', '127.0']);
echo $ip_host; //display '127.0.0.1'

Host::createFromLabels(['0.1', '127.0'], Host::IS_ABSOLUTE);
//throws InvalidArgumentException
~~~

### Host::createFromIp

This method allow creating an Host object from an IP. If the submitted IP is invalid a `InvalidArgumentException` exception is thrown.

~~~php
<?php

use League\Uri\Components\Host;

$ipv4 = Host::createFromIp('127.0.0.1');
echo $ipv4; //display '127.0.0.1'

$ipv6 = Host::createFromIp('::1');
echo $ipv6; //display '[::1]'

Host::createFromIp('uri.thephpleague.com');
//throws InvalidArgumentException
~~~

## Normalization

Whenever you create a new host your submitted data is normalized using non desctructive operations:

- the host is lowercased;
- the host is converted to its ascii representation;
- the bracket are added if necessary if you are instantiating a IPv6 Host wit the `createFromIp` named constructor;

~~~php
<?php

use League\Uri\Components\Host;

$host = Host::createFromLabels(['com', 'ExAmPle', 'shop']);
echo $host; //display 'shop.example.com'

$host = Host::createFromLabels(['be', 'bébé']);
echo $host; //display 'xn--bb-bjab.be'

~~~

## Host types

### IP address or registered name

There are two type of host:

- Hosts represented by an IP;
- Hosts represented by a registered name;

To determine what type of host you are dealing with the `Host` class provides the `isIp` method:

~~~php
<?php

use League\Uri\Components\Host;
use League\Uri\Schemes\Http as HttpUri;

$host = Host::createFromIp('::1');
$host->isIp();   //return true

$alt_host = new Host('example.com');
$host->isIp(); //return false;

(new Host(HttpUri::createFromServer($_SERVER)->getHost()))->isIp(); //return a boolean
~~~

### IPv4 or IPv6

Knowing that you are dealing with an IP is good, knowing that its an IPv4 or an IPv6 is better.

~~~php
<?php

use League\Uri\Components\Host;

$ipv6 = Host::createFromIp('::1');
$ipv6->isIp();   //return true
$ipv6->isIpv4(); //return false
$ipv6->isIpv6(); //return true

$ipv4 = new Host('127.0.0.1');
$ipv4->isIp();   //return true
$ipv4->isIpv4(); //return true
$ipv4->isIpv6(); //return false
~~~

The object can also detect if the IPv6 has a zone identifier or not. This can be handy if you want to know if you need to remove it or not for security reason.

~~~php
<?php

use League\Uri\Components\Host;

$ipv6 = new Host('[Fe80::4432:34d6:e6e6:b122%eth0-1]');
$ipv6->hasZoneIdentifier(); //return true

$ipv4 = new Host('127.0.0.1');
$ipv4->hasZoneIdentifier(); //return false
~~~

### Relative or fully qualified domain name

If you don't have a IP then you are dealing with a host name. A host name is a [domain name](http://tools.ietf.org/html/rfc1034) subset according to [RFC1123](http://tools.ietf.org/html/rfc1123#section-2.1). As such a host name can not, for example, contain an `_`.

A host name is considered absolute or as being a fully qualified domain name (FQDN) if its string representation ends with a `.`, otherwise it is known as being a relative or a partially qualified domain name (PQDN).

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('example.com');
$host->isIp();       //return false
$host->isAbsolute(); //return false

$fqdn = new Host('example.com.');
$fqdn->isIp();       //return false
$fqdn->isAbsolute(); //return true

$ip = new Host('[::1]');
$ip->isIp();       //return true
$ip->isAbsolute(); //return false
~~~

## Host representations

### Collection representation

A host can be splitted into its different labels. The class provides an array representation of a the host labels using the `Host::getLabels` method. If the host is an IP, the array contains only one entry, the full IP.

<p class="message-notice">The class uses a hierarchical representation of the Hostname. This mean that the host top-level domain is the array first item.</p>

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('secure.example.com');
$arr = $host->getLabels(); //return  ['com', 'example', 'secure'];

$fqdn = new Host('secure.example.com.');
$arr = $fqdn->getLabels(); //return ['com', 'example', 'secure'];

$host = new Host('[::1]');
$arr = $host->getLabels(); //return ['::1'];
~~~

<p class="message-warning">Once in array representation you can not distinguish a partially from a fully qualified domain name.</p>

## Accessing host contents

### Countable and IteratorAggregate

The class provides several methods to works with its labels. The class implements PHP's `Countable` and `IteratorAggregate` interfaces. This means that you can count the number of labels and use the `foreach` construct to iterate over them.

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('secure.example.com');
count($host); //return 3
foreach ($host as $offset => $label) {
    echo $labels; //will display "com", then "example" and last "secure"
}
~~~

### Label keys

If you are interested in getting all the label keys you can do so using the `Host::keys` method like shown below:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('uk.example.co.uk');
$host->keys();        //return [0, 1, 2, 3];
$host->keys('uk');    //return [0, 3];
$host->keys('gweta'); //return [];
~~~

The methods returns all the label keys, but if you supply an argument, only the keys whose label value equals the argument are returned.

### Label content

If you are only interested in a given label you can access it directly using the `Host::getLabel` method as show below:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('example.co.uk');
$host->getLabel(0);         //return 'uk'
$host->getLabel(23);        //return null
$host->getLabel(23, 'now'); //return 'now'
~~~

<p class="message-notice"><code>Host::getLabel</code> always returns the <code>RFC3987</code> label representation.</p>

If the offset does not exists it will return the value specified by the optional second argument or `null`.

This method supports negative offsets

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('example.co.uk');
$host->getLabel(-1);         //return 'uk'
$host->getLabel(-23);        //return null
$host->getLabel(-23, 'now'); //return 'now'
~~~

### Host public informations

Using data from [the public suffix list](http://publicsuffix.org/) and the [PHP Domain Parser](https://github.com/jeremykendall/php-domain-parser) library every `Host` object can:

- return the subdomain using the `Host::getSubdomain` method;
- return the registerable domain using the `Host::getRegisterableDomain` method;
- return the public suffix using the `Host::getPublicSuffix` method;
- tell you if the found public suffix is valid using the `Host::isPublicSuffixValid` method;

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('www.example.co.uk');
echo $host->getPublicSuffix();        //display 'co.uk'
echo $host->getRegisterableDomain();  //display 'example.co.uk'
echo $host->getSubdomain();           //display 'www'
$host->isPublicSuffixValid();         //return a boolean 'true' in this example
~~~

If the data is not found the methods listed above will all return an **empty string** except for the `Host::isPublicSuffixValid` method which will return `false`.

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('192.158.26.30');
echo $host->getPublicSuffix();        //return ''
echo $host->getRegisterableDomain();  //return ''
echo $host->getSubdomain();           //return ''
$host->isPublicSuffixValid();         //return false
~~~

## Modifying the host

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">When a modification fails an <code>InvalidArgumentException</code> exception is thrown.</p>

### Append labels

To append labels to the current host you need to use the `Host::append` method. This method accepts a single argument which represents the data to be appended. This data can be:

- another `Host` object;
- a string;

~~~php
<?php

use League\Uri\Components\Host;

$host    = new Host();
$newHost = $host->append('toto')->append(new Host('example.com'));
$newHost->__toString(); //return toto.example.com
~~~

<p class="message-notice">This method is used by the URI modifier <code>appendLabels</code></p>

### Prepend labels

To prepend labels to the current host you need to use the `Host::prepend` method. This method accept a single argument which represents the data to be prepended. This data can be:

- another `Host` object;
- an object which implements the `__toString` method;
- a string;

~~~php
<?php

use League\Uri\Components\Host;

$host    = new Host();
$newHost = $host->prepend('example.com')->prepend(new Host('toto'));
$newHost->__toString(); //return toto.example.com
~~~

<p class="message-notice">This method is used by the URI modifier <code>prependLabels</code></p>

### Replace label

To replace a label you must use the `Host::replace` method with two arguments:

- The label's key to replace if it exists.
- The data to replace the key with. This data can be:
    - another `Host` object;
    - an object which implements the `__toString` method;
    - a string;

~~~php
<?php

use League\Uri\Components\Host;

$host    = new Host('foo.example.com');
$newHost = $host->replace(2, 'bar.baz');
$newHost->__toString(); //return bar.baz.example.com
~~~

<p class="message-warning">if the specified offset does not exist, no modification is performed and the current object is returned.</p>

<p class="message-notice">This method is used by the URI modifier <code>replaceLabel</code></p>

### Remove labels

To remove labels from the current object you can use the `Host::without` method. This method expects a single argument and will returns a new `Host` object without the selected labels. The argument is an array containing a list of offsets to remove.

~~~php
<?php

use League\Uri\Components\Host;

$host    = new Host('toto.example.com');
$newHost = $host->without([1]);
$newHost->__toString(); //return toto.com
~~~

<p class="message-warning">if the specified offsets do not exist, no modification is performed and the current object is returned.</p>

<p class="message-notice">This method is used by the URI modifier <code>RemoveSegments</code></p>

### Remove zone identifier

According to [RFC6874](http://tools.ietf.org/html/rfc6874#section-4):

> You **must** remove any ZoneID attached to an outgoing URI, as it has only local significance at the sending host.

To fullfill this requirement, the `Host::withoutZoneIdentifier` method is provided. The method takes not parameter and return a new host instance without its zone identifier. If the host has not zone identifier, the current instance is returned unchanged.

~~~php
<?php

use League\Uri\Components\Host;

$host    = new Host('[fe80::1%25eth0-1]');
$newHost = $host->withoutZoneIdentifier();
echo $newHost; //displays '[fe80::1]';
~~~

<p class="message-notice">This method is used by the URI modifier <code>RemoveZoneIdentifier</code></p>

### Filter labels

You can filter the `Host` object using the `Host::filter` method. Filtering is done using the same arguments as PHP's `array_filter`.

You can filter the host according to its labels values:

~~~php
<?php

use League\Uri\Components\Host;

$host    = new Host('www.11.be');
$newHost = $host->filter(function ($value) {
    return !is_numeric($value);
});
echo $newHost; //displays 'www.be'
~~~

You can filter the host according to its labels key.

~~~php
<?php

use League\Uri\Components\Host;

$host    = new Host('www.11.be');
$newHost = $host->filter(function ($value) {
    return $value != 2;
}, ARRAY_FILTER_USE_KEY);
echo $newHost; //displays '11.be'
~~~

You can filter the host according to its label value and key.

~~~php
<?php

use League\Uri\Components\Host;

$host    = new Path('media.bbc.co.uk');
$newHost = $query->filter(function ($value, $key) {
    return 1 != $key && strpos($value, 'e') === false;
}, ARRAY_FILTER_USE_BOTH);
echo $newHost; //displays 'bbc.uk'
~~~

The second argument is the same as the one used by `array_filter` since `PHP 5.6.0`.

By default, if no flag is specified the method will filter the host using the host label content.

<p class="message-notice">This method is used by the URI modifier <code>FilterLabels</code></p>


### Update the RegisterableDomain

You can update the registerable domain part of the host. Just make sure that the update is not a FQDN. The method accepts `null` or a valid host. 

~~~php
<?php

use League\Uri\Components\Host;

$host    = new Host('www.11.be');
$newHost = $host->withRegisterableDomain('co.uk');
echo $newHost; //displays 'www.11.co.uk'
~~~

<p class="message-notice">This method is used by the URI modifier <code>RegisterableDomain</code></p>

### Update the Host subdomains

You can update the subdomain part of the host. Just make sure that the update is not a FQDN. And that the Host is not an IP otherwise a `InvalidArgumentException` will be thrown. The method accepts `null` or a valid host.

~~~php
<?php

use League\Uri\Components\Host;

$host    = new Host('www.11.be');
$newHost = $host->withSubdomain('shop');
echo $newHost; //displays 'shop.11.be'
~~~

<p class="message-notice">This method is used by the URI modifier <code>Subdomain</code></p>

### Update the Root label

To update the host state from FDQN to a RDQN and vice-versa you can use 2 methods

- `withRootLabel`
- `withoutRootLabel`

These methods which takes not argument add or remove the root empty label from the host as see below:

~~~php
<?php

use League\Uri\Components\Host;

$host = new Host('www.11.be');
echo $host->withRootLabel() //display 'www.11.be.'
echo $host->withoutRootLabel() //display 'www.11.be'
~~~

<p class="message-notice">Theses methods are used by the URI modifiers <code>AddRootLabel</code> and <code>RemoveRootLabel</code></p>
