---
layout: default
title: Installation
---

# Installation

## System Requirements

* **PHP >= 7.0.13** but the latest stable version of PHP is recommended;
* `mbstring` extension;
* `intl` extension;

## Install

The library is available on [Packagist][] and should be installed using [Composer][]. This can be done by running the following command on a composer installed box:

~~~bash
$ composer require league/uri
~~~

Most modern frameworks will include Composer out of the box, but ensure the following file is included:

~~~php
<?php

// Include the Composer autoloader
require 'vendor/autoload.php';
~~~

## Packages

The URI meta package contains:

the following **fully** decoupled libraries:

- [League Uri Hostname Parser](https://github.com/thephpleague/uri-hostname-parser)
- [League Uri Interfaces](https://github.com/thephpleague/uri-interfaces)
- [League Uri Parser](https://github.com/thephpleague/uri-parser)

the following libraries:

- [League Uri Schemes](https://github.com/thephpleague/uri-schemes)
- [League Uri Components](https://github.com/thephpleague/uri-components)
- [League Uri Manipulations](https://github.com/thephpleague/uri-manipulations)

[Packagist]: https://packagist.org/packages/league/uri
[Composer]: https://getcomposer.org/
[PSR-4]: https://php-fig.org/psr/psr-4/