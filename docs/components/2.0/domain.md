---
layout: default
title: The Domain component
---

The Domain Host
=======

The library provides a `Domain` class to ease domain host creation and manipulation.

The class validates domain names according to [RFC952](https://tools.ietf.org/html/rfc952) and [RFC1123](https://tools.ietf.org/html/rfc1123#page-13)

In addition it exposes:
 
- the [package common API](/components/2.0/api/), 
- the [Host common API](/components/2.0/host/),

but also provide specific methods to work with a URI domain host component.

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Exceptions\SyntaxError</code> exception is thrown.</p>

## Creating a new object using the default constructor

~~~php
<?php
public Domain::__construct($host): void
~~~

<p class="message-notice">submitted string is normalized to be <code>RFC3986</code> compliant.</p>
<p class="message-warning">The <code>$host</code> can not be <code>null</code> or the empty string as they represents an invalid domain name.</p>

## The Domain Host API

The following methods can be used to further characterize your domain host.

~~~php
public static Domain::createFromLabels(iterable $data): self
public Domain::isAbsolute(): bool
public Domain::labels(): array
public Domain::get(int $offset): ?string
public Domain::keys(?string $label = null): array
public Domain::count(): int
public Domain::getIterator(): iterator
public Domain::withRootLabel(): self
public Domain::withoutRootLabel(): self
public Domain::prepend(string $host): self
public Domain::append(string $host): self
public Domain::replaceLabel(int $offset, string $host): self
public Domain::withoutLabels(array $offsets): self
~~~

### Domain::createFromLabels

A host is a collection of labels delimited by the host separator `.`. So it is possible to create a `Host` object using a collection of labels with the `Domain::createFromLabels` method.
The method expects a single arguments, a collection of label. **The labels must be ordered hierarchically, this mean that the array should have the top-level domain in its first entry**.

<p class="message-warning">Since an IP is not a hostname, the class will throw an <code>League\Uri\Exceptions\SyntaxError</code> if you try to create an fully qualified domain name with a valid IP address.</p>

~~~php
$host = Domain::createFromLabels(['com', 'example', 'shop']);
echo $host; //display 'shop.example.com'

$fqdn = Domain::createFromLabels(['', 'com', 'example', 'shop']);
echo $fqdn; //display 'shop.example.com.'

Domain::createFromLabels(['0.1', '127.0']);
//throws League\Uri\Exceptions\SyntaxError
~~~

### Partial or fully qualified domain name

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

To update the host state from FQDN to a PQDN and vice-versa you can use 2 methods

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

<p class="message-warning">The last example depends on the presence of the <code>ext-intl</code> extension. Otherwise the code will trigger a <code>IdnSupportMissing</code> exception</p>

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
$host = (new Domain('toto'))->append('example.com');
echo $host; //return toto.example.com
~~~

#### Prepending labels

To prepend labels to the current host you need to use the `Domain::prepend` method. This method accept a single argument which represents the data to be prepended. This data can be a string or `null`.

~~~php
$host = (new Domain('example.com'))->prepend('toto');
echo $host; //return toto.example.com
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
