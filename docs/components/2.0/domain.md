---
layout: default
title: The Domain component
---

The Host
=======

The library provides a `Domain` class to ease domain host creation and manipulation. This object exposes the [package common API](/components/1.0/api/), but also provide specific methods to work with the URI domain host component.

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Exceptions\SyntaxError</code> exception is thrown.</p>

## Creating a new object using the default constructor

~~~php
<?php
public Domain::__construct(?string $content = null): void
~~~

<p class="message-notice">submitted string is normalized to be <code>RFC3986</code> compliant.</p>

## Host default accessible methods

~~~php
<?php
public Domain::getIp(void): ?string;
public Domain::getIpVersion(void): ?string
public Domain::isIp(void): bool
public Domain::isDomain(void): bool
public Domain::toAscii(): ?string;
public Domain::toUnicode(): ?string;
~~~

### IP or registered name

There are two (2) types of host:

- Hosts represented by an IP;
- Hosts represented by a registered name;

To determine what type of host you are dealing with the `Host` class provides the `isIp` method:

~~~php
<?php

use League\Uri\Components\Domain;

$host = new Domain('example.com');
$host->isIp(); //return false;
$ip_host = $host->withContent('127.0.0.1');
$ip_host->isIp(); //return true;
~~~

### Getting the IP string representation

You can retrieve the IP string representation from the Host object using the `getIp` method. If the Host is not an IP `null` will be returned instead.

~~~php
$host = new Domain('[fe80::1%25eth0-1]');
$host->getIp(); //returns 'fe80::1%eth0-1'

$newHost = $host->withContent('uri.thephpleague.com');
$newHost->getIp();        //returns null
$newHost->getIpVersion(); //returns null
~~~

## Host represented by a registered name

If you don't have a IP then you are dealing with a registered name. A registered name can be a [domain name](http://tools.ietf.org/html/rfc1034) subset if it follows [RFC1123](http://tools.ietf.org/html/rfc1123#section-2.1) but it is not a requirement as stated in [RFC3986](https://tools.ietf.org/html/rfc3986#section-3.2.2)

> (...) URI producers should use names that conform to the DNS syntax, even when use of DNS is not immediately apparent, and should limit these names to no more than 255 characters in length.

<p class="message-info"><code>Domain::isDomain</code> is available since version <code>1.8.0</code>.</p>

~~~php
<?php
public Domain::isDomain(void): bool
~~~

To determine if a host is a domain name or a general registered name you just need to use the newly added method `Domain::isDomain`

~~~php
$domain = new Domain('www.example.co.uk');
$domain->isDomain();  //return true

$reg_name = new Domain('...test.com');
$reg_name->isDomain();  //return false
~~~

## Host represented by a domain name

<p class="message-warning"><code>Domain::getRegisterableDomain</code> and <code>Domain::withRegisterableDomain</code> are deprecated and replaced by <code>Domain::getRegistrableDomain</code> and <code>Domain::withRegistrableDomain</code> starting with version <code>1.5.0</code>.</p>

If you don't have an IP or a general registered name it means you are using a domain name. As such the following method can be used to further caracterize your host.

~~~php
public static Domain::createFromLabels(iterable $data): self
public Domain::isAbsolute(void): bool
public Domain::labels(void): array
public Domain::get(int $offset): ?string
public Domain::keys(?string $label = null): array
public Domain::count(void): int
public Domain::getIterator(void): iterator
public Domain::withRootLabel(void): self
public Domain::withoutRootLabel(void): self
public Domain::prepend(string $host): self
public Domain::append(string $host): self
public Domain::replaceLabel(int $offset, string $host): self
public Domain::withoutLabels(array $offsets): self
~~~

### Domain::createFromLabels

A host is a collection of labels delimited by the host separator `.`. So it is possible to create a `Host` object using a collection of labels with the `Domain::createFromLabels` method.
The method expects a single arguments, a collection of label. **The labels must be ordered hierarchically, this mean that the array should have the top-level domain in its first entry**.

<p class="message-warning">Since an IP is not a hostname, the class will throw an <code>League\Uri\Components\Exception</code> if you try to create an fully qualified domain name with a valid IP address.</p>

~~~php
$host = Domain::createFromLabels(['com', 'example', 'shop']);
echo $host; //display 'shop.example.com'

$fqdn = Domain::createFromLabels(['', 'com', 'example', 'shop']);
echo $fqdn; //display 'shop.example.com.'

Domain::createFromLabels(['0.1', '127.0']);
//throws League\Uri\Exceptions\SyntaxError
~~~

### Partial or fully qualified registered name

A host is considered absolute or as being a fully qualified domain name (FQDN) if it contains a <strong>root label</strong>, its string representation ends with a `.`, otherwise it is known as being a relative or a partially qualified domain name (PQDN).

~~~php
$host = new Domain('example.com');
$host->isIp();       //return false
$host->isAbsolute(); //return false

$fqdn = new Domain('example.com.');
$fqdn->isIp();       //return false
$fqdn->isAbsolute(); //return true
~~~

#### Updating the host status

To update the host state from FDQN to a PQDN and vice-versa you can use 2 methods

- `withRootLabel`
- `withoutRootLabel`

These methods which takes not argument add or remove the root empty label from the host as see below:

~~~php
$host = new Domain('www.11.be');
echo $host->withRootLabel() //display 'www.11.be.'
echo $host->withoutRootLabel() //display 'www.11.be'
~~~

### Normalization

Whenever you create a new host your submitted data is normalized using non desctructive operations:

- the host is lowercased;
- the host is converted to its ascii representation;

~~~php
$host = Domain::createFromLabels(['com', 'ExAmPle', 'shop']);
echo $host; //display 'shop.example.com'

$host = Domain::createFromLabels(['be', 'bébé']);
echo $host; //display 'xn--bb-bjab.be'
~~~

### Accessing the Host labels

#### Host iterable representation

A host can be splitted into its different labels. The class provides an array representation of a the host labels using the `Domain::getLabels` method.

<p class="message-info">If the host is an IP, the array contains only one entry, the full IP.</p>

<p class="message-notice">The class uses a hierarchical representation of the Hostname. This mean that the host top-level domain is the array first item.</p>

~~~php
$host = new Domain('secure.example.com');
$host->labels(); //return  ['com', 'example', 'secure'];

$fqdn = new Domain('secure.example.com.');
$fqdn->labels(); //return ['', 'com', 'example', 'secure'];
~~~

The class also implements PHP's `Countable` and `IteratorAggregate` interfaces. This means that you can count the number of labels and use the `foreach` construct to iterate over them.

~~~php
$host = new Domain('secure.example.com');
count($host); //return 3
foreach ($host as $offset => $label) {
    echo $labels; //will display "com", then "example" and last "secure"
}
~~~

<p class="message-info">The returned label is encoded following <code>RFC3987</code>.</p>

#### Accessing Host label offset

If you are interested in getting the label offsets you can do so using the `Domain::keys` method.

~~~php
$host = new Domain('uk.example.co.uk');
$host->keys();        //return [0, 1, 2, 3];
$host->keys('uk');    //return [0, 3];
$host->keys('gweta'); //return [];
~~~

The method returns all the label keys, but if you supply an argument, only the keys whose label value equals the argument are returned.

<p class="message-info">The supplied argument is <code>RFC3987</code> encoded to enable matching the corresponding keys.</p>

#### Accessing Host label value

If you are only interested in a given label you can access it directly using the `Domain::get` method as show below:

~~~php
$host = new Domain('example.co.uk');
$host->get(0);  //return 'uk'
$host->get(23); //return null
~~~

<p class="message-notice"><code>Domain::get</code> always returns the <code>RFC3987</code> label representation.</p>

If the offset does not exists it will return `null`.

<p class="message-info"><code>Domain::get</code> supports negative offsets</p>

~~~php
$host = new Domain('example.co.uk');
$host->get(-1);         //return 'uk'
$host->get(-23);        //return null
~~~

### Manipulating the host labels

#### Appending labels

To append labels to the current host you need to use the `Domain::append` method. This method accepts a single argument which represents the data to be appended. This data can be a string or `null`.

~~~php
$host    = new Domain();
$newHost = $host->append('toto')->append('example.com');
echo $newHost; //return toto.example.com
~~~

#### Prepending labels

To prepend labels to the current host you need to use the `Domain::prepend` method. This method accept a single argument which represents the data to be prepended. This data can be a string or `null`.

~~~php
$host    = new Domain();
$newHost = $host->prepend('example.com')->prepend('toto');
echo $newHost; //return toto.example.com
~~~

#### Replacing labels

To replace a label you must use the `Domain::replaceLabel` method with two arguments:

- The label's key to replace if it exists **MUST BE** an integer.
- The data to replace the key with. This data must be a string or `null`.

~~~php
$host    = new Domain('foo.example.com');
$newHost = $host->replaceLabel(2, 'bar.baz');
echo $newHost; //return bar.baz.example.com
~~~

<p class="message-info">Just like the <code>Domain::get</code> this method supports negative offset.</p>

<p class="message-warning">if the specified offset does not exist, no modification is performed and the current object is returned.</p>

#### Removing labels

To remove labels from the current object you can use the `Domain::withoutLabels` method. This method expects variadic integer offset representing the labals offset to remove and will returns a new `Host` object without the selected labels.

~~~php
$host    = new Domain('toto.example.com');
$newHost = $host->withoutLabels(0, 2);
$newHost->__toString(); //return example
~~~

<p class="message-info">Just like the <code>Domain::get</code> this method supports negative offset.</p>

<p class="message-warning">if the specified offsets do not exist, no modification is performed and the current object is returned.</p>
