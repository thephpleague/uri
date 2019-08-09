---
layout: default
title: IPv4 Host Normalizer
---

IPv4 Host Normalizer
=======

The `League\Uri\IPv4HostNormalizer` is a userland PHP IPv4 Host Normalizer.

```php
<?php

use League\Uri\IPv4HostNormalizer;
use League\Uri\Components\Host;

$host = new Host('0');
$normalizedHost = IPv4HostNormalizer::normalize($host);
echo $host; // returns 0
echo $normalizedHost; // returns 0.0.0.0
```

Usage
--------

<p class="message-notice">The normalization algorithms uses the <a href="https://url.spec.whatwg.org/#concept-ipv4-parser">WHATWG rules</a> to parse and format IPv4 multiple string representations into a valid IPv4 decimal representation.</p>

### Description

```php
<?php

use League\Uri\IPv4HostNormalizer;
use League\Uri\Contracts\HostInterface;
use League\Uri\Contracts\IpHostInterface;
use League\Uri\Maths\Math;

public static function IPv4HostNormalizer::normalize(HostInterface $host, ?Math $math = null): IpHostInterface;
```

The `IPv4HostNormalizer::normalize` method parameters are:

- `$host` a `League\Uri\Contracts\HostInterface` implementing object;
- `$math` a `League\Uri\Maths\Math` implementing object;

The `League\Uri\Maths\Math` is responsible for making all the calculation needed to perform the conversion between IPv4 string representation.
The package comes bundle with two implementation:

- `League\Uri\Maths\GMPMath` which relies on GMP extension;
- `League\Uri\Maths\PHPMath` which relies on PHP build against a x.64 architecture;

If not `League\Uri\Maths\Math` implementing object is provided the class will try to load one of it's these implementations.
If it can not a `League\Uri\Exceptions\Ipv4CalculatorMissing` exception will be thrown.

```php
<?php

use League\Uri\IPv4HostNormalizer;
use League\Uri\Components\Host;
use League\Uri\Maths\PHPMath;

$host = new Host('0300.0250.0000.0001');
$normalizedHost = IPv4HostNormalizer::normalize($host, new PHPMath());
echo $host; // returns '0300.0250.0000.0001'
echo $normalizedHost; // returns '192.168.0.1'

//will throw a League\Uri\Exceptions\Ipv4CalculatorMissing on a x.32 PHP build
```
