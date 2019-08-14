---
layout: default
title: IPv4 Normalizer
---

IPv4 Normalizer
=======

The `League\Uri\IPv4Normalizer` is a userland PHP IPv4 Host Normalizer.

```php
<?php

use League\Uri\IPv4Normalizer;
use League\Uri\Components\Host;

$host = new Host('0');
$normalizer = new IPv4Normalizer();
$normalizedHost = $normalizer->normalizeHost($host);
echo $host; // returns 0
echo $normalizedHost; // returns 0.0.0.0
```

Usage
--------

<p class="message-notice">The normalization algorithms uses the <a href="https://url.spec.whatwg.org/#concept-ipv4-parser">WHATWG rules</a> to parse and format IPv4 multiple string representations into a valid IPv4 decimal representation.</p>

### Description

```php
<?php

use League\Uri\Contracts\AuthorityInterface;
use League\Uri\Contracts\HostInterface;
use League\Uri\Contracts\UriInterface;
use League\Uri\IPv4Normalizer;
use League\Uri\IPv4Calculators\IPv4Calculator;
use \Psr\Http\Message\UriInterface as Psr7UriInterface;

public function IPv4Normalizer::__construct(IPv4Calculator $calculator = null);
public function IPv4Normalizer::normalizeUri(UriInterface|Psr7UriInterface $uri): UriInterface|Psr7UriInterface ;
public function IPv4Normalizer::normalizeAuthority(AuthorityInterface $host): AuthorityInterface;
public function IPv4Normalizer::normalizeHost(HostInterface $host): HostInterface;
```

The `IPv4Normalizer::normalize*` methods single parameters are object that contains or is a host component.

The `League\Uri\IPv4Calculators\IPv4Calculator` is responsible for making all the calculation needed to perform the conversion between IPv4 string representation.
The package comes bundle with two implementation:

- `League\Uri\IPv4Calculators\GMPCalculator` which relies on GMP extension;
- `League\Uri\IPv4Calculators\BCMathCalculator` which relies on BCMath extension;
- `League\Uri\IPv4Calculators\NativeCalculator` which relies on PHP build against a x.64 architecture;

If not `League\Uri\IPv4Calculators\IPv4Calculator` implementing object is provided the class will try to load one of it's these implementations.
If it can not a `League\Uri\Exceptions\Ipv4CalculatorMissing` exception will be thrown.

The methods always returns a instance of the same type as the submitted one with the host changed if the normalization is applicable or unchanged otherwise.

```php
<?php

use League\Uri\IPv4Normalizer;
use League\Uri\Components\Host;
use League\Uri\IPv4Calculators\NativeCalculator;

$authority = new Authority('hello:world@0300.0250.0000.0001:442');
$normalizer = new IPv4Normalizer(new NativeCalculator());
$normalizedAuthority = $normalizer->normalizeAuthority($authority);

echo $authority->getHost(); // returns '0300.0250.0000.0001'
echo $normalizedAuthority->getHost(); // returns '192.168.0.1'

//will throw a League\Uri\Exceptions\Ipv4CalculatorMissing on a x.32 PHP build
```
