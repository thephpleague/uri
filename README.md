League Uri
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri.svg?style=flat-square)](https://github.com/thephpleague/uri)

This package contains concrete URI objects represented as immutable value object. 

- An URI object implementing the `League\Uri\Contract\UriInterface` interface as defined in the [uri-interfaces package](https://github.com/thephpleague/uri-interfaces)
- An URI object implementing the `Psr\Http\Message\UriInterface` from [PSR-7](http://www.php-fig.org/psr/psr-7/).

System Requirements
-------

You need:

- **PHP >= 7.2** but the latest stable version of PHP is recommended
- the `mbstring` extension
- the `intl` extension

Dependencies
-------

- [League URI Interfaces](https://github.com/thephpleague/uri-interfaces)
- [PSR-7](https://www.php-fig.org/psr/psr-7/)

You should also require the **ext-intl** if you are dealing with i18n URI.

Installation
--------

```
$ composer require league/uri
```

Documentation
--------

Full documentation can be found at [uri.thephpleague.com](http://uri.thephpleague.com).


Contributing
-------

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

Testing
-------

The library has a :

- a [PHPUnit](https://phpunit.de) test suite
- a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/).
- a code analysis compliance test suite using [PHPStan](https://github.com/phpstan/phpstan).

To run the tests, run the following command from the project folder.

``` bash
$ composer test
```

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
