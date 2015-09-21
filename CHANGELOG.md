#Changelog

All Notable changes to `League\Url` will be documented in this file

## Next - 2015-XX-XX

### Added

- `Intl` extension is now required to use the library
- `FileInfo` extension is now required to use the library
- Domain parsing capabilities to `Host` using `jeremykendall/php-domain-parser` package
- A `UriParser` class to parse an URI according to RFC3986 rules, the library no longer depend on PHP `parse_url` function
- The `QueryParser` is now a standalone class to parse and build a query string according to RFC3986 rules.
- `League\Uri\Schemes\Generic\AbstractUri` to enable better URI extension
- Modifiers classes to modify URI objects in an uniform way for interoperability
- A `Data` class to specifically manipulate `data` schemed URI
- A `Http` class to specifically manipulate `http`,`https` schemed URI
- A `Ftp` class to specifically manipulate `ftp` schemed URI
- A `Ws` class to specifically manipulate `ws`, `wss` schemed URI
- A `DataPath` component class to manipulate Data-uri path component
- A `HierarchicalPath` to manipulate Hierarchical-like path component
- Support for IPv6 zone identifier
- Re-introduced `Host::toAscii` and adding `Host::isIdn` method
- API to add/remove the `Path` leading and trailing slash.
- `Query::ksort` method
- Missing `User` and `Pass` Interfaces
- `Host::getLiteral` to get the raw IP representation of a Ip Literal hostname
- `Path::relativize` to generate relative path

### Fixed

- Move namespace from `League\Url` to `League\Uri` to avoid dependency hell
- Renamed methods for consistency with PHP naming conventions
- userinfo string representation `:` delimiter was added unnecessarily
- Host::__toString return the hostname in Unicode or ASCII depending on the user submission
- Host::toUnicode now returns a new Host instance
- Host now support append/prepend/replacing to or with IPv4 Host type
- `HierarchicalPath` now supports multiple leading slash
- Formatter::format accept `Uri`, PSR-7 `UriInterface` and `UriPart` implemented objects
- `Host` now iterate from the top-level label to the last subdomain (from right to left)
- The `Query::merge` method only accept `League\Uri\Interfaces\Query` objects or query string
- `Collection::filter` method can now use PHP's `array_filter` constants in PHP 5.6+

### Remove

- `Uri::isAbsolute`
- `Uri::sameValueAs`
- `Scheme::isSupported`, `Scheme::getStandardPort`, `Port::getStandardSchemes`.
- Support for `PHP 5.4`
- All partial modifier methods attached to an URI object are removed and replaced by URI Modifiers classes

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
