---
layout: default
title: The Host component
---

The Host
=======

The library provides a `Host` class to ease host creation and manipulation. This URI component object exposes the [package common API](/components/2.0/api/), but also provide specific methods to work with the URI host component.

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Exceptions\SyntaxError</code> exception is thrown.</p>

## Creating a new object using the default constructor

~~~php
<?php
public Host::__construct($content = null): void
~~~

<p class="message-notice">submitted string is normalized to be <code>RFC3986</code> compliant.</p>

## Basic properties and methods

The following methods will always be available independently of the Host type. They define the host main features and properties.

~~~php
public Host::getIp(): string
public Host::getIpVersion(): ?string
public Host::isIp(): bool
public Host::isDomain(): bool
public Host::toAscii(): ?string
public Host::toUnicode(): ?string
~~~

Hosts can be:
 
- a registered name;
- a domain name;
- an IP address;

## Host represented by a registered name

If you don't have a IP then you are dealing with a registered name. A registered name can be a [domain name](http://tools.ietf.org/html/rfc1034) subset if it follows [RFC1123](http://tools.ietf.org/html/rfc1123#section-2.1) but it is not a requirement as stated in [RFC3986](https://tools.ietf.org/html/rfc3986#section-3.2.2)

> (...) URI producers should use names that conform to the DNS syntax, even when use of DNS is not immediately apparent, and should limit these names to no more than 255 characters in length.

~~~php
public Host::isDomain(): bool
~~~

To determine if a host is a domain name and not a general registered name or an IP address, you just need to use the `Host::isDomain` method.

~~~php
$domain = new Host('www.example.co.uk');
$domain->isDomain();  //return true

$reg_name = new Host('...test.com');
$reg_name->isDomain();  //return false
~~~

### Normalization

Whenever you create a new host your submitted data is normalized using non destructive operations:

- the host is lowercased;
- the host is converted to its ascii representation;

~~~php
$host = new Host('ShOp.ExAmPle.COM');
echo $host; //display 'shop.example.com'

$host = new Host('BéBé.be');
echo $host; //display 'xn--bb-bjab.be'
~~~

<p class="message-warning">The last example depends on the presence of the <code>ext-intl</code> extension. Otherwise the code will trigger a <code>IdnSupportMissing</code> exception</p>

At any given time you can access the ascii or unicode Host representation using the two (2) following methods:

~~~php
$host = new Host('BéBé.be');
echo $host; //display 'xn--bb-bjab.be'
echo $host->toUnicode();  //displays bébé.be
echo $host->toAscii();  //displays 'xn--bb-bjab.be'
~~~

<p class="message-warning">Both methods depends on the presence of the <code>ext-intl</code> extension. Otherwise the code will trigger a <code>IdnSupportMissing</code> exception</p>

## Host represented by an IP

~~~php
public static Host::createFromIp(string $ip, string $version = '', Math $math = null): self
public Host::isIpv4(): bool
public Host::isIpv6(): bool
public Host::isIpFuture(): bool
public Host::hasZoneIdentifier(): bool
public Host::withoutZoneIdentifier(): self
~~~

### Host::createFromIp

This method allows creating an Host object from an IP.

~~~php
$ipv4 = Host::createFromIp('127.0.0.1');
echo $ipv4; //display '127.0.0.1'

$ipv6 = Host::createFromIp('::1');
echo $ipv6; //display '[::1]'

Host::createFromIp('uri.thephpleague.com');
//throws League\Uri\Exceptions\SyntaxError
~~~

The method can also infer the IPv4 from its hexadecimal or octal representation. 

~~~php
use League\Uri\Components\Host;
use League\Uri\Maths\GMPMath;

$ipv4 = Host::createFromIp('999999999', '', new GMPMath());
echo $ipv4; //display '59.154.201.255'
~~~

This normalization works using:
 
- a `League\Uri\Maths\Math` implementing object to calculate the IP address like shown below;
- WHATWG IPv4 host parsing rules;

You can skip providing such object if:

- **the GMP extension is installed and configured** or
- **you are using a x.64 build of PHP**

<p class="message-warning">A <code>RuntimeException</code> will be trigger if no <code>League\Uri\Maths\Math</code> is provided or can not be detected</p>.

~~~php
$ipv4 = Host::createFromIp('999999999');
echo $ipv4; //display '59.154.201.255'
//will work on supported platform 
~~~

<p class="message-warning">This normalization is destructive and thus is never apply internally on a instantiated <code>Host</code> object.</p>

### IPv4 or IPv6

There are two (2) types of host:

- Hosts represented by an IP;
- Hosts represented by a registered name;

To determine what type of host you are dealing with the `Host` class provides the `isIp` method:

~~~php
$host = new Host('example.com');
$host->isIp(); //return false;
$ip_host = $host->withContent('127.0.0.1');
$ip_host->isIp(); //return true;
~~~

Knowing that you are dealing with an IP is good, knowing its version is better.

~~~php
$ipv6 = Host::createFromIp('::1');
$ipv6->isIp();       //return true
$ipv6->isIpv4();     //return false
$ipv6->isIpv6();     //return true
$ipv6->isIpFuture(); //return false
$ipv6->getIpVersion(); //return '6'

$ipv4 = new Host('127.0.0.1');
$ipv4->isIp();       //return true
$ipv4->isIpv4();     //return true
$ipv4->isIpv6();     //return false
$ipv4->isIpFuture(); //return false
$ipv4->getIpVersion(); //return '4'

$ipfuture = new Host('v32.1.2.3.4');
$ipfuture->isIp();       //return true
$ipfuture->isIpv4();     //return false
$ipfuture->isIpv6();     //return false
$ipfuture->isIpFuture(); //return true
$ipfuture->getIpVersion(); //return '32'

$domain = new Host('thephpleague.com'):
$domain->isIp();       //return false
$domain->isIpv4();     //return false
$domain->isIpv6();     //return false
$domain->isIpFuture(); //return false
$domain->getIpVersion(); //return null
~~~

### Zone Identifier

#### Detecting the presence of the Zone Identifier

The object can also detect if the IPv6 has a zone identifier or not. This can be handy if you want to know if you need to remove it or not for security reason.

~~~php
$ipv6 = new Host('[Fe80::4432:34d6:e6e6:b122%eth0-1]');
$ipv6->hasZoneIdentifier(); //return true

$ipv4 = new Host('127.0.0.1');
$ipv4->hasZoneIdentifier(); //return false
~~~

#### Removing the Zone Identifier

According to [RFC6874](http://tools.ietf.org/html/rfc6874#section-4):

> You **must** remove any ZoneID attached to an outgoing URI, as it has only local significance at the sending host.

To fullfill this requirement, the `Host::withoutZoneIdentifier` method is provided. The method takes not parameter and return a new host instance without its zone identifier. If the host has not zone identifier, the current instance is returned unchanged.

~~~php
$host    = new Host('[fe80::1%25eth0-1]');
$newHost = $host->withoutZoneIdentifier();
echo $newHost; //displays '[fe80::1]';
~~~

### Getting the IP string representation

You can retrieve the IP string representation from the Host object using the `getIp` method. If the Host is not an IP `null` will be returned instead.

~~~php
$host = new Host('[fe80::1%25eth0-1]');
$host->getIp(); //returns 'fe80::1%eth0-1'

$newHost = $host->withContent('uri.thephpleague.com');
$newHost->getIp();        //returns null
$newHost->getIpVersion(); //returns null
~~~
