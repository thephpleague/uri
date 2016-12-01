Uri Schemes
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri-schemes/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-schemes)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-schemes.svg?style=flat-square)](https://github.com/thephpleague/uri-components/schemes)

This package contains concrete URI objects represented as immutable value object. Each URI object implements the `League\Uri\Interfaces\Uri` interface as defined in the [uri-interfaces package](https://github.com/thephpleague/uri-interfaces) or the `Psr\Http\Message\UriInterface` from [PSR-7](http://www.php-fig.org/psr/psr-7/).

System Requirements
-------

You need:

- **PHP >= 5.6.0** but the latest stable version of PHP is recommended
- the `mbstring` extension
- the `intl` extension

Dependencies
-------

- [PSR-7](http://www.php-fig.org/psr/psr-7/)
- [uri-interfaces](https://github.com/thephpleague/uri-interfaces)
- [uri-parser](https://github.com/thephpleague/uri-parser)

Installation
--------

```
$ composer require league/uri-schemes
```

Documentation
--------

The following URI objects are defined (order alphabetically):

- `League\Uri\Schemes\Data` : represents a Data scheme URI
- `League\Uri\Schemes\File` : represents a File scheme URI
- `League\Uri\Schemes\FTP` : represents a FTP scheme URI
- `League\Uri\Schemes\Http` : represents a HTTP/HTTPS scheme URI
- `League\Uri\Schemes\Ws` : represents a WS/WSS scheme URI

Usage
-------

### Creating new URI objects

To instantiate a new URI object you can use two named constructors:

```php
<?php
public Uri::createFromString(string $uri = ''): Uri
public Uri::createFromComponents(array $components): Uri
```

- The `Uri::createFromString` named constructor returns an new URI object from a string.
- The `Uri::createFromComponents` named constructor returns an new URI object from the return value of PHP’s function `parse_url`.

#### Http::createFromServer

The `League\Uri\Schemes\Http` class can be instantiated using the server variables using `Http::createFromServer`.

```php
<?php

use League\Uri\Schemes\Http as HttpUri;

//don't forget to provide the $_SERVER array
$uri = HttpUri::createFromServer($_SERVER);
```

**The method only relies on the server's safe parameters to determine the current URI. If you are using the library behind a proxy the result may differ from your expectation as no `$_SERVER['HTTP_X_*']` header is taken into account for security reasons.**

#### Data::createFromPath

The `League\Uri\Schemes\Data` class can be instantiated from a filepath.

```php
<?php

use League\Uri\Schemes\Data as DataUri;

$uri = DataUri::createFromPath('path/to/my/png/image.png');
echo $uri; //returns 'data:image/png;charset=binary;base64,...'
//where '...' represent the base64 representation of the file
```

**If the file is not readable or accessible an `InvalidArgumentException` exception will be thrown. The class uses PHP’s `finfo` class to detect the required mediatype as defined in RFC2045.**

#### File::createFromUnixPath and File::createFromWindowsPath

The `League\Uri\Schemes\File` comes with two optionals named constructors:

- The `File::createFromUnixPath` to return a new object from a Unix Path.
- The `File::createFromWindowsPath` to return a new object from a Windows Path.

```php
<?php

use League\Uri\Schemes\File as FileUri;

$uri = FileUri::createFromWidowsPath(c:\windows\My Documents\my word.docx);
echo $uri; //returns 'file:///c:My%20Documents/my%20word.docx'
```

All URI objects expose the same methods.

### Accessing URI properties

You can access the URI string, its individual parts and components using their respective getter methods.

```php
<?php

public Uri::__toString(): string
public Uri::getScheme(void): string
public Uri::getUserInfo(void): string
public Uri::getHost(void): string
public Uri::getPort(void): int|null
public Uri::getAuthority(void): string
public Uri::getPath(void): string
public Uri::getQuery(void): string
public Uri::getFragment(void): string
```

Which will lead to the following result for a simple HTTP URI:

```php
<?php

use League\Uri\Schemes\Http;

$uri = Http::createFromString("http://foo:bar@www.example.com:81/how/are/you?foo=baz#title");
echo $uri;                 //displays "http://foo:bar@www.example.com:81/how/are/you?foo=baz#title"
echo $uri->getScheme();    //displays "http"
echo $uri->getUserInfo();  //displays "foo:bar"
echo $uri->getHost();      //displays "www.example.com"
echo $uri->getPort();      //displays 81 as an integer
echo $uri->getAuthority(); //displays "foo:bar@www.example.com:81"
echo $uri->getPath();      //displays "/how/are/you"
echo $uri->getQuery();     //displays "foo=baz"
echo $uri->getFragment();  //displays "title"
```

### Modifying URI properties

To replace one of the URI part you can use the modifying methods exposed by all URI object. If the modifications do not alter the current object, it is returned as is, otherwise, a new modified object is returned.

**The method will trigger a `InvalidArgumentException` exception if the resulting URI is not valid. The modification validaity is scheme dependant.**

```php
<?php

public Uri::withScheme(string $scheme): self
public Uri::withUserInfo(string $user [, string $password = null]): self
public Uri::withHost(string $host): self
public Uri::withPort(int|null $port): self
public Uri::withPath(string $path): self
public Uri::withQuery(string $query): self
public Uri::withFragment(string $fragment): self
```

Since All URI object are immutable you can chain each modifying methods to simplify URI creation and/or modification.

```php
<?php

use League\Uri\Schemes\Ws;

$uri = Ws::createFromString("ws://thephpleague.com/fr/")
    ->withScheme("wss")
    ->withUserInfo("foo", "bar")
    ->withHost("www.example.com")
    ->withPort(81)
    ->withPath("/how/are/you")
    ->withQuery("foo=baz");

echo $uri; //displays wss://foo:bar@www.example.com:81/how/are/you?foo=baz
```
