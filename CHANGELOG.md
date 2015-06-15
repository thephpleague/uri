#Changelog

All Notable changes to `League\Url` will be documented in this file

## 4.0.0-beta-4 - 2015-06-XX

### Added

- A system to manage schemes registration using the `SchemeRegistry` class.

### Remove

- `Scheme::isSupported`
- Remove `file`, `gopher` and `ssh` schemes. If needed you can use the Scheme registration system to add them.
- `Port::getStandardSchemes` use the `SchemeRegistry` class to get this information.
- `Scheme::getStandardPort` use the `SchemeRegistry` class to get this information.
- `Scheme::hasStandardPort` use the `SchemeRegistry` class to get this information.

## 4.0.0-beta-3 - 2015-06-09

### Added

- `isEmpty` method to `League\Url\Interfaces\Url` to tell whether a URL is empty or not
- `isSupported` static method to `League\Url\Scheme` to tell whether a specified scheme is supported by the library
- Improve decoding invalid punycode host labels
- Add support for `gopher` scheme

## Fixed

- Invalid Punycode should still be allowed and not produce any error [issue #73](https://github.com/thephpleague/url/issues/73)

## 4.0.0-beta-2 - 2015-06-05

## Fixed
- remove useless optional argument from `Path::getUriComponent`

## 4.0.0-beta-1 - 2015-06-03

### Added

- Package structure is changed to better reflect the importance of each component.

- `League\Url\Interfaces\Url`
    -  now implements `Psr\Http\Message\UriInterface`
    - `resolve` to create new URL from relative URL
    - add proxy methods to ease partial component modifications

- `League\Url\Interfaces\UrlPart`
    -  UrlParts implementing object can be compared using the `sameValueAs`

- `League\Url\Interfaces\Component`
    - `modify` to create a new instance from a given component;

- `League\Url\Interfaces\CollectionComponent`:
    - The interface is simplified to remove ambiguity when manipulating Host and Path objects.

- `League\Url\Interfaces\Host`:
    - implements IPv4 and IPv6 style host
    - `__toString` method now always return the ascii version of the hostname

- `League\Url\Interfaces\Path`:
    - `withoutDotSegment` remove dot segment according to RFC3986 rules;
    - `withoutDuplicateDelimiters` remove multiple adjacent delimiters;
    - `getBasename` returns the trailing path;
    - manage the trailing path extension using `getExtension` and `withExtension`;

- `League\Url\Interfaces\Query`:
    - The interface is simplified to remove ambiguity and allow setting default values for missing keys;
    - The object no longer depends on php `parse_str`

- `League\Url\Interfaces\Scheme` and `League\Url\Interfaces\Port`:
    - support for listing and detecting standard port for a given scheme in both objects with
        - `Interfaces\Port::getStandardSchemes`
        - `Interfaces\Port::useStandardScheme`
        - `Interfaces\Scheme::getStandardPorts`
        - `Interfaces\Scheme::hasStandardPort`

- `League\Url\UserInfo` class added to better manipulate URL user info part

- The `Url` class as well as all components classes are now immutable value objects.
- The `League\Url\Output\Formatter` class is added to ease Url formatting
- The package is more RFC3986 compliant

### Deprecated
- Nothing

### Fixed
- Handling of legacy hostname suffixed with a "." when using `Url::createFromServer`

### Remove
- `League\Url\Components\User` and `League\Url\Components\Pass`
- Support for `PHP 5.3`
- `UrlImmutable` class
- Most of the public API is removed :
    - to comply to `RFC3986`;
    - to enable immutable value object;
    - to implement `PSR7` UriInterface;

## 3.3.2 - 2015-05-13

### Fixed

- Bug fix URL parsing [issue #65](https://github.com/thephpleague/url/issues/65)

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