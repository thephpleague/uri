URI
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri)
[![HHVM Status](https://img.shields.io/hhvm/league/uri.svg?style=flat-square)](http://hhvm.h4cc.de/package/league/uri)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/uri.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/uri/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/uri.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/uri)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri.svg?style=flat-square)](https://github.com/thephpleague/uri/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/league/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)

The `Uri` package provides simple and intuitive classes to create and manage URIs in PHP.

Highlights
------

- Simple API
- [RFC3986](http://tools.ietf.org/html/rfc3986) compliant
- Implements the `UriInterface` from [PSR-7][]
- Fully documented
- Framework Agnostic
- Composer ready, [PSR-2][] and [PSR-4][] compliant

Documentation
------

Full documentation can be found at [uri.thephpleague.com](http://uri.thephpleague.com). Contribute to this documentation in the [gh-pages](https://github.com/thephpleague/uri/tree/gh-pages) branch

System Requirements
-------

You need:

- **PHP >= 5.5.0** or **HHVM >= 3.6**, but the latest stable version of PHP/HHVM is recommended
- the `mbstring` extension
- the `intl` extension

To use the library.

Install
-------

Install `Uri` using Composer.

```
$ composer require league/uri
```

Testing
-------

`Uri` has a [PHPUnit](https://phpunit.de) test suite and a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/). To run the tests, run the following command from the project folder.

``` bash
$ composer test
```

Contributing
-------

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

Security
-------

If you discover any security related issues, please email nyamsprod@gmail.com instead of using the issue tracker.

Credits
-------

- [ignace nyamagana butera](https://github.com/nyamsprod)
- [All Contributors](https://github.com/thephpleague/uri/contributors)

License
-------

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/
[PSR-7]: http://www.php-fig.org/psr/psr-7/
