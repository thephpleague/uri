URI
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri.svg?style=flat-square)](https://github.com/thephpleague/uri/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/league/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)

The `Uri` package provides simple and intuitive classes to create and manage URIs in PHP.


Highlights
------

- Simple API
- [RFC3986](http://tools.ietf.org/html/rfc3986) and [RFC3987](http://tools.ietf.org/html/rfc3987) compliant
- Implements the `UriInterface` from [PSR-7][]
- Fully documented
- Framework Agnostic
- Composer ready, [PSR-2][] and [PSR-4][] compliant

Documentation
------

Full documentation can be found at [uri.thephpleague.com](http://uri.thephpleague.com).

System Requirements
-------

You need:

- **PHP >= 7.0.13** but the latest stable version of PHP is recommended
- the `mbstring` extension
- the `intl` extension

To use the library.

Install
-------

**We no longer recommend installing this package directly.**

The package is a metapackage that aggregates all components related to processing and manipulating URI in PHP; in most cases, you will want a subset, and these may be installed separately.

The following components are part of the metapackage:

- [League Uri Parser](https://github.com/thephpleague/uri-parser/)
- [League Uri Schemes](https://github.com/thephpleague/uri-schemes/)
- [League Uri Components](https://github.com/thephpleague/uri-components/)
- [League Uri Manipulations](https://github.com/thephpleague/uri-manipulations/)
- [League Uri Hostname Parser](https://github.com/thephpleague/uri-hostname-parser) *since version 5.2 in replacement of PHP Domain Parser version 3.0*

The primary use case for installing the entire suite is when upgrading from a version 4 release.

If you decide you still want to install the entire suite use Composer and run the following command on a composer installed box:

~~~bash
$ composer require league/uri
~~~

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
