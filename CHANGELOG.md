#Changelog

All Notable changes to `League\Url` will be documented in this file

## 4.X - XXXX-XX-XX

### Added

- Package structure is changed better reflect the importance of each component.

- `League\Url\Interfaces\Url`
    -  now extends `Psr\Http\Message\UriInterface`
    - `normalize` to normalize a URL returns a new Url interface normalized;
    - `hasStandardPort` which returns `true` if the standard port for a given `scheme` is used.
    - `resolve` to create new URL from relative URL

- `League\Url\Interfaces\Component`
    -  different component implementing object can be compared using the `sameValueAs`
    - `withValue` to create a new instance from a given component;

- `League\Url\Interfaces\Segment`:
    - The interface is simplified to remove ambiguity when manipulating Host and Path objects.

- `League\Url\Interfaces\Host`:
    - implements IPv4 and IPV6 style host

- `League\Url\Interfaces\Path`:
    - `normalize` the path according to RFC3986 rules;
    - `getBasename` returns the trailing path;
    - manage the trailing path extension using `getExtension` and `withExtension`;

- `League\Url\Interfaces\Query`:
    - The interface is simplified to remove ambiguity and allow setting default values for missing keys;

- The `Url` class as well as all Components classes are now immutable value objects.
- The `League\Url\Util\Formatter` class is added to ease Url formatting
- The package is more RFC3986 compliant

### Deprecated
- Nothing

### Fixed
- Handling of legacy hostname suffixed with a "." when using `Url::createFromServer`

### Remove
- Support for `PHP 5.3`
- `UrlImmutable` class
- Most of the public API is removed :
    - to comply to `RFC3986`;
    - to enable immutable value object;
    - to implement `PSR7` UriInterface;

## 3.3.1 - 2015-03-26

### Fixed
- `League\Url\Components\Query` bug fix [issue #58](https://github.com/thephpleague/url/issues/58), improved bug fix [issue #31](https://github.com/thephpleague/url/issues/31)

## 3.3.0 - 2015-03-20

### Added
- adding the `toArray` method to `League\Url\AbstractUrl` to output the URL like PHP native `parse_url` [issue #56](https://github.com/thephpleague/url/issues/56)

### Fixed
- `League\Url\Components\Query` bug fix remove parameter only if the value equals `null` [issue #58](https://github.com/thephpleague/url/issues/58)

## 3.2.1 - 2014-11-27

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- `League\Url\AbstractUrl\createFromServer` bug fix handling of `$_SERVER['HTTP_HOST']`

### Remove
- Nothing

### Security
- Nothing

## 3.2.0 - 2014-11-12

### Added
- adding the following methods to `League\Url\AbstractUrl`
    - `getUserInfo`
    - `getAuthority`
    - `sameValueAs`

### Deprecated
- Nothing

### Fixed
- `League\Url\Components\Fragment::__toString` encoding symbols according to [RFC3986](http://tools.ietf.org/html/rfc3986#section-3.5)

### Remove
- Nothing

### Security
- Nothing

## 3.1.1 - 2014-09-02

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- `parse_str` does not preserve key params

### Remove
- Nothing

### Security
- Nothing

## 3.1.0 - 2014-07-10

### Added
- Adding IDN support using `True\Punycode` package
- The library now **requires** the `mbstring` extension to work.

The following methods were added:

- `League\Url\Components\Host::toAscii`
- `League\Url\Components\Host::toUnicode` as an alias of `League\Url\Components\Host::__toString`

### Deprecated
- Nothing

### Fixed
- invalid URI parsing

### Remove
- Nothing

### Security
- Nothing

## 3.0.1 - 2014-06-31

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- invalid URI parsing

### Remove
- Nothing

### Security
- Nothing

## 3.0 - 2014-06-25

New Release, complete rewrite from `Bakame\Url`