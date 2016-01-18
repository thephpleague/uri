---
layout: default
title: The Host component
---

# The Host component

The library provides a `League\Uri\Components\Host` class to ease complex host manipulation.

## Host creation

### Using the default constructor

A new `League\Uri\Components\Host` object can be instantiated using the default constructor.

~~~php
use League\Uri\Components\Host;

$host = new Host('shop.example.com');
echo $host; //display 'shop.example.com'

$fqdn = new Host('shop.example.com.');
echo $fqdn; //display 'shop.example.com.'

$ipv4 = new Host('127.0.0.1');
echo $ipv4; //display '127.0.0.1'

$ipv6 = new Host('::1');
echo $ipv6; //display '[::1]'

$ipv6_alt = new Host('[::1]');
echo $ipv6_alt; //display '[::1]'
~~~

<p class="message-warning">If the submitted value is not a valid host an <code>InvalidArgumentException</code> will be thrown.</p>

### Using a League Uri object

You can also access a `Host` object from a Uri object:

~~~php
use League\Uri\Schemes\Http as HttpUri;

$uri  = HttpUri::createFromString('http://url.thephpleague.com/');
$host = $uri->host; // $host is a League\Uri\Components\Host object;
~~~

### Using a named constructor

A host is a collection of labels delimited by the host separator `.`. So it is possible to create a `Host` object using a collection of labels with the `Host::createFromArray` method.

The method expects at most 2 arguments:

- The first required argument must be a collection of label (an `array` or a `Traversable` object). **The labels must be ordered hierarchically, this mean that the array should have the top-level domain in its first entry**. 

- The second optional argument, a `Host` constant, tells whether this is an <abbr title="Fully Qualified Domain Name">FQDN</abbr> or not:
    - `Host::IS_ABSOLUTE` creates an a fully qualified domain name `Host` object;
    - `Host::IS_RELATIVE` creates an a partially qualified domain name `Host` object;

By default this optional argument equals to `Host::IS_RELATIVE`.

<p class="message-warning">Since an IP is not a hostname, the class will throw an <code>InvalidArgumentException</code> if you try to create an fully qualified domain name with a valid IP address.</p>

~~~php
use League\Uri\Components\Host;

$host = Host::createFromArray(['com', 'example', 'shop']);
echo $host; //display 'shop.example.com'

$fqdn = Host::createFromArray(['com', 'example', 'shop'], Host::IS_ABSOLUTE);
echo $fqdn; //display 'shop.example.com.'

$ip_host = Host::createFromArray(['0.1', '127.0']);
echo $ip_host; //display '127.0.0.1'

Host::createFromArray(['0.1', '127.0'], Host::IS_ABSOLUTE);
//throws InvalidArgumentException
~~~

## Normalization

Whenever you create a new host your submitted data is normalized using non desctructive operations:

- the host is lowercased;
- the bracket are added if necessary if you are instantiating a IPv6 Host;

~~~php
use League\Uri\Components\Host;

$host = Host::createFromArray(['com', 'ExAmPle', 'shop']);
echo $host; //display 'shop.example.com'

$ipv6 = new Host('::1');
echo $ipv6; //display '[::1]'
~~~

## Host types

### IP address or hostname

There are two type of host:

- Hosts represented by an IP;
- Hosts represented by a hostname;

To determine what type of host you are dealing with the `Host` class provides the `isIp` method:

~~~php
use League\Uri\Components\Host;
use League\Uri\Schemes\Http as HttpUri;

$host = new Host('::1');
$host->isIp();   //return true

$alt_host = new Host('example.com');
$host->isIp(); //return false;

HttpUri::createFromServer($_SERVER)->host->isIp(); //return a boolean
~~~

### IPv4 or IPv6

Knowing that you are dealing with an IP is good, knowing that its an IPv4 or an IPv6 is better.

~~~php
use League\Uri\Components\Host;

$ipv6 = new Host('::1');
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
use League\Uri\Components\Host;

$ipv6 = new Host('Fe80::4432:34d6:e6e6:b122%eth0-1');
$ipv6->hasZoneIdentifier(); //return true

$ipv4 = new Host('127.0.0.1');
$ipv4->hasZoneIdentifier(); //return false
~~~

### Relative or fully qualified domain name

If you don't have a IP then you are dealing with a host name. A host name is a [domain name](http://tools.ietf.org/html/rfc1034) subset according to [RFC1123](http://tools.ietf.org/html/rfc1123#section-2.1). As such a host name can not, for example, contain an `_`.

A host name is considered absolute or as being a fully qualified domain name (FQDN) if its string representation ends with a `.`, otherwise it is known as being a relative or a partially qualified domain name (PQDN).

~~~php
use League\Uri\Components\Host;

$host = new Host('example.com');
$host->isIp();       //return false
$host->isAbsolute(); //return false

$fqdn = new Host('example.com.');
$fqdn->isIp();       //return false
$fqdn->isAbsolute(); //return true

$ip = new Host('::1');
$ip->isIp();       //return true
$ip->isAbsolute(); //return false
~~~

## Host representations

### String representation

Basic host representations is done using the following methods:

~~~php
use League\Uri\Components\Host;

$host = new Host('example.com');
$host->__toString();      //return 'example.com'
$host->getUriComponent(); //return 'example.com'
$host->getLiteral();      //return 'example.com'

$ipv4 = new Host('127.0.0.1');
$ipv4->__toString();      //return '127.0.0.1'
$ipv4->getUriComponent(); //return '127.0.0.1'
$ipv4->getLiteral();      //return '127.0.0.1'

$ipv6 = new Host('::1');
$ipv6->__toString();      //return '[::1]'
$ipv6->getUriComponent(); //return '[::1]'
$ipv6->getLiteral();      //return '::1'
~~~

<p class="message-notice">The <code>Host::getLiteral</code> method is useful to get the host raw IP representation without the IPv6 brackets for instance.</p>

### IDN support

The `Host` class supports the <a href="http://en.wikipedia.org/wiki/Internationalized_domain_name" target="_blank"><abbr title="Internationalized Domain Name">IDN</abbr></a> mechanism.

At any given time the object can tell you if the submitted hostname is a IDN or not using the `Host::isIdn()` method.

~~~php
use League\Uri\Components\Host;

$idn_host = new Host('스타벅스코리아.com');       //you set a IDN hostname
echo $idn_host->isIdn(); //return true

$host = new Host('xn--mgbh0fb.xn--kgbechtv');  //you set a ascii hostname
echo $host->isIdn();     //return false

$host = new Host('192.168.2.56');              //you set a IP host
echo $host->isIdn();     //return false
~~~

### Array representation

A host can be splitted into its different labels. The class provides an array representation of a the host labels using the `Host::toArray` method. If the host is an IP, the array contains only one entry, the full IP.

<p class="message-notice">The class uses a hierarchical representation of the Hostname. This mean that the host top-level domain is the array first item.</p>

~~~php
use League\Uri\Components\Host;

$host = new Host('secure.example.com');
$arr = $host->toArray(); //return  ['com', 'example', 'secure'];

$fqdn = new Host('secure.example.com.');
$arr = $fqdn->toArray(); //return ['com', 'example', 'secure'];

$host = new Host('::1');
$arr = $host->toArray(); //return ['::1'];
~~~

<p class="message-warning">Once in array representation you can not distinguish a partially from a fully qualified domain name.</p>

## Accessing host contents

### Countable and IteratorAggregate

The class provides several methods to works with its labels. The class implements PHP's `Countable` and `IteratorAggregate` interfaces. This means that you can count the number of labels and use the `foreach` construct to iterate over them.

~~~php
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
use League\Uri\Components\Host;

$host = new Host('uk.example.co.uk');
$host->keys();        //return [0, 1, 2, 3];
$host->keys('uk');    //return [0, 3];
$host->keys('gweta'); //return [];
~~~

The methods returns all the label keys, but if you supply an argument, only the keys whose label value equals the argument are returned.

To know If a key exists before using it you can use the `Host::hasKey` method which returns `true` or `false` depending on the presence or absence of the submitted key in the current object.

~~~php
use League\Uri\Components\Host;

$host = new Host('uk.example.co.uk');
$host->hasKey(2);  //return true
$host->hasKey(23); //return false
~~~

### Label content

If you are only interested in a given label you can access it directly using the `Host::getLabel` method as show below:

~~~php
use League\Uri\Components\Host;

$host = new Host('example.co.uk');
$host->getLabel(0);         //return 'uk'
$host->getLabel(23);        //return null
$host->getLabel(23, 'now'); //return 'now'
~~~

If the offset does not exists it will return the value specified by the optional second argument or `null`.

### Host public informations

Using data from [the public suffix list](http://publicsuffix.org/) and the [PHP Domain Parser](https://github.com/jeremykendall/php-domain-parser) library every `Host` object can:

- return the subdomain using the `Host::getSubdomain` method;
- return the registerable domain using the `Host::getRegisterableDomain` method;
- return the public suffix using the `Host::getPublicSuffix` method;
- tell you if the found public suffix is valid using the `Host::isPublicSuffixValid` method;

~~~php
use League\Uri\Components\Host;

$host = new Host('www.example.co.uk');
echo $host->getPublicSuffix();        //display 'co.uk'
echo $host->getRegisterableDomain();  //display 'example.co.uk'
echo $host->getSubdomain();           //display 'www'
$host->isPublicSuffixValid();         //return a boolean 'true' in this example
~~~

If the data is not found the methods listed above will all return `null` except for the `Host::isPublicSuffixValid` method which will return `false`.

~~~php
use League\Uri\Components\Host;

$host = new Host('192.158.26.30');
echo $host->getPublicSuffix();        //return 'null'
echo $host->getRegisterableDomain();  //return 'null'
echo $host->getSubdomain();           //return 'null'
$host->isPublicSuffixValid();         //return false
~~~

## Modifying the host

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

<p class="message-warning">When a modification fails an <code>InvalidArgumentException</code> exception is thrown.</p>

### Transcode the host

You can transcode the Host so that its string representation match the IDN support you need.

- To transcode the host into an ASCII representation use the `Host::toAscii` method.
- To transcode the host into an IDN representation use the `Host::toUnicode` method.

~~~php
use League\Uri\Components\Host;

$idn_host = new Host('스타벅스코리아.com'); //you set a IDN hostname
echo $idn_host->__toString();            //display '스타벅스코리아.com'
echo $idn_host->toAscii()->__toString(); //display 'xn--oy2b35ckwhba574atvuzkc.com'
echo $idn_host->toUnicode->__toString(); //display '스타벅스코리아.com'

$host = new Host('xn--mgbh0fb.xn--kgbechtv');  //you set a ascii hostname
echo $host->__toString();              //display 'xn--mgbh0fb.xn--kgbechtv'
echo $host->toAscii()->__toString();   //display 'xn--mgbh0fb.xn--kgbechtv'
echo $host->toUnicode()->__toString(); //display 'مثال.إختبار'
~~~

<p class="message-notice">These methods are used by the URI modifiers <code>HotToAscii</code> and <code>HotToUnicode</code></p>

### Append labels

To append labels to the current host you need to use the `Host::append` method. This method accepts a single argument which represents the data to be appended. This data can be:

- another `Host` object;
- a string;

~~~php
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
use League\Uri\Components\Host;

$host    = new Host('www.11.be');
$newHost = $host->filter(function ($value) {
	return !is_numeric($value);
}, Host::FILTER_USE_VALUE);
echo $newHost; //displays 'www.be'
~~~

You can filter the host according to its labels key.

~~~php
use League\Uri\Components\Host;

$host    = new Host('www.11.be');
$newHost = $host->filter(function ($value) {
	return $value != 2;
}, Host::FILTER_USE_KEY);
echo $newHost; //displays '11.be'
~~~

You can filter the host according to its label value and key.

~~~php
use League\Uri\Components\Host;

$host    = new Path('media.bbc.co.uk');
$newHost = $query->filter(function ($value, $key) {
    return 1 != $key && strpos($value, 'e') === false;
}, Path::FILTER_USE_BOTH);
echo $newHost; //displays 'bbc.uk'
~~~

By specifying the second argument flag you can change how filtering is done:

- use `Host::FILTER_USE_VALUE` to filter according to the label value;
- use `Host::FILTER_USE_KEY` to filter according to the label offset;
- use `Host::FILTER_USE_BOTH` to filter according to the label value and offset;

By default, if no flag is specified the method will filter the host using the `Host::FILTER_USE_VALUE` flag.

<p class="message-info">If you are in PHP 5.6+ you can substitute these constants with PHP's <code>array_filter</code> flags constants <code>ARRAY_FILTER_USE_KEY</code> and <code>ARRAY_FILTER_USE_BOTH</code></p>

<p class="message-notice">This method is used by the URI modifier <code>FilterLabels</code></p>
