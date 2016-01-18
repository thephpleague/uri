---
layout: default
title: Installation
---

# Installation

## System Requirements

* **PHP >= 5.5.9** but the latest stable version of PHP is recommended;
* `mbstring` extension;
* `intl` extension;

## Install

The library is available on [Packagist][] and should be installed using [Composer][]. This can be done by running the following command on a composer installed box:

~~~
$ composer require league/uri
~~~

Most modern frameworks will include Composer out of the box, but ensure the following file is included:

~~~php
// Include the Composer autoloader
require 'vendor/autoload.php';
~~~

## Going Solo

You can also use the library without Composer by:

- heading to the [releases page](https://github.com/thephpleague/uri/releases)
- selecting your version and downloading it in your preferred format.

Once extracted you will be able to load the library using any [PSR-4][] compatible autoloader.

If you choose to install the package manually, you are responsible for installing and autoloading the following dependencies:

- [PHP Domain Parser](https://github.com/jeremykendall/php-domain-parser)

[Packagist]: https://packagist.org/packages/league/uri
[Composer]: https://getcomposer.org/
[PSR-4]: https://php-fig.org/psr/psr-4/