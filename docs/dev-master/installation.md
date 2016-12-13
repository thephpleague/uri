---
layout: default
title: Installation
---

# Installation

## System Requirements

* **PHP >= 5.6.4** but the latest stable version of PHP is recommended;
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

## Dependencies

This library depends on:

- [PHP Domain Parser](https://github.com/jeremykendall/php-domain-parser)
- [league-uri-interfaces](https://github.com/thephpleague/uri-interfaces)
- [league-uri-parser](https://github.com/thephpleague/uri-parser)
- [league-uri-schemes](https://github.com/thephpleague/uri-schemes)
- [league-uri-components](https://github.com/thephpleague/uri-components)
- [league-uri-manipulations](https://github.com/thephpleague/uri-manipulations)

[Packagist]: https://packagist.org/packages/league/uri
[Composer]: https://getcomposer.org/
[PSR-4]: https://php-fig.org/psr/psr-4/