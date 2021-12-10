URI
=======

[![Build](https://github.com/thephpleague/uri/workflows/build/badge.svg)](https://github.com/thephpleague/uri/actions?query=workflow%3A%22build%22)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri.svg?style=flat-square)](https://github.com/thephpleague/uri/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/league/uri.svg?style=flat-square)](https://packagist.org/packages/league/uri)

The `Uri` package provides simple and intuitive classes to manage URIs in PHP. You will be able to

- parse, build and resolve URIs
- create URIs from different sources (string, PHP environment, base URI, URI template, ...);
- handle internalisation;
- infer properties and features from URIs;

````php
<?php

use League\Uri\UriTemplate;

$template = 'https://api.twitter.com:443/{version}/search/{term:1}/{term}/{?q*,limit}#title';
$defaultVariables = ['version' => '1.1'];
$params = [
    'term' => 'john',
    'q' => ['a', 'b'],
    'limit' => '10',
];

$uriTemplate = new UriTemplate($template, $defaultVariables);
$uri = $uriTemplate->expand($params);
// $uri is a League\Uri\Uri object

echo $uri->getScheme();    //displays "https"
echo $uri->getAuthority(); //displays "api.twitter.com:443"
echo $uri->getPath();      //displays "/1.1/search/j/john/"
echo $uri->getQuery();     //displays "q=a&q=b&limit=10"
echo $uri->getFragment();  //displays "title"
echo $uri;
//displays "https://api.twitter.com:443/1.1/search/j/john/?q=a&q=b&limit=10#title"
echo json_encode($uri);
//displays "https:\/\/api.twitter.com:443\/1.1\/search\/j\/john\/?q=a&q=b&limit=10#title"
````

Highlights
------

- Simple API
- [RFC3986][], [RFC3987][] and [RFC6570][] compliant
- Implements the `UriInterface` from [PSR-7][]
- Fully documented
- Framework Agnostic

System Requirements
-------

- You require **PHP >= 7.3** but the latest stable version of PHP is recommended
- You will need the **ext-intl** to handle i18n URI.
- Since version 6.2.0 you will need the **ext-fileinfo** to handle Data URI creation from a filepath.

Dependencies
-------

- [League URI Interfaces](https://github.com/thephpleague/uri-interfaces)
- [PSR-7][]

In order to handle IDN host you are required to also install the `intl` extension otherwise an exception will be thrown when attempting to validate such host.

In order to create Data URI from a filepath, since version `6.2`, you are required to also install the `fileinfo` extension otherwise an exception will be thrown.

Installation
--------

```
$ composer require league/uri
```

Documentation
--------

Full documentation can be found at [uri.thephpleague.com][].

Contributing
-------

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CODE OF CONDUCT](.github/CODE_OF_CONDUCT.md) for details.

Testing
-------

The library has a :

- a [PHPUnit](https://phpunit.de) test suite
- a coding style compliance test suite using [PHP CS Fixer](https://cs.sensiolabs.org/).
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

Attribution
-------

The `UriTemplate` class is adapted from the [Guzzle 6][] project. 

License
-------

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[PSR-7]: https://www.php-fig.org/psr/psr-7/
[RFC3986]: https://tools.ietf.org/html/rfc3986
[RFC3987]: https://tools.ietf.org/html/rfc3987
[RFC6570]: https://tools.ietf.org/html/rfc6570
[uri.thephpleague.com]: https://uri.thephpleague.com
[Guzzle 6]: https://github.com/guzzle/guzzle/blob/6.5/src/UriTemplate.php
