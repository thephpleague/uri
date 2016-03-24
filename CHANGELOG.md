# Changelog

All Notable changes to `League\Uri` will be documented in this file

## 4.1.1 - 2016-03-24

### Added

- Nothing

### Fixed

- Bug fix path encoding [issue #38](https://github.com/thephpleague/uri/issues/38)

### Deprecated

- Nothing

### Remove

- Nothing

## 4.1.0 - 2016-02-18

### Added

- `Formatter::preserveQuery` to improve query inclusion in URI string representation
- `Formatter::preserveFragment` to improve fragment inclusion in URI string representation
- `Formatter::__invoke` as an alias of `Formatter::format`
- `UriParser::__invoke` as an alias of `UriParser::parse`

### Fixed

- Improve Uri Component modification [issue #29](https://github.com/thephpleague/uri/issues/29)
- Improve Path encoding/decoding [issue #28](https://github.com/thephpleague/uri/issues/28)
- Improve lowercase transformation in hostname [issue #27](https://github.com/thephpleague/uri/issues/27)
- Fix empty string evaluation [issue #31](https://github.com/thephpleague/uri/issues/31)

### Deprecated

- `Formatter::getHostEncoding`
- `Formatter::getQueryEncoding`
- `Formatter::getQuerySeparator`
- `Modifiers\Filters\Flag::withFlags`
- `Modifiers\Filters\ForCallbable::withCallable`
- `Modifiers\Filters\ForCallbable::withCallable`
- `Modifiers\Filters\Keys::withKeys`
- `Modifiers\Filters\Label::withLabel`
- `Modifiers\Filters\Offset::withOffset`
- `Modifiers\Filters\QueryString::withQuery`
- `Modifiers\Filters\Segment::withSegment`
- `Modifiers\Filters\Uri::withUri`
- `Modifiers\DataUriParameters\withParameters`
- `Modifiers\Extension\withExtension`
- `Modifiers\KsortQuery\withAlgorithm`
- `Modifiers\Typecode\withType`

### Remove

- Nothing

## 4.0.1 - 2015-11-03

### Added

- Nothing

### Fixed

- `User` and `Pass` encoding
- `Http::createFromServer` handling userinfo when not using `mod_php` with `$_SERVER['HTTP_AUTHORIZATION']`
- `UriParser` handling URI strings with invalid scheme
- `QueryParser` handling numeric index [issue #25](https://github.com/thephpleague/uri/issues/25)
- `DataPath` mimetype syntax validation [issue #21](https://github.com/thephpleague/uri/issues/21)
- `DataPath::withParameters` the `;base64` binary code now always throw an `InvalidArgumentException`

### Deprecated

- Nothing

### Remove

- Nothing

## 4.0.0 - 2015-09-23

### Added

- `Intl` extension is now required to use the library
- `FileInfo` extension is now required to use the library
- Domain parsing capabilities to `Host` using `jeremykendall/php-domain-parser` package
- `UriParser` to parse an URI according to RFC3986 rules
- `QueryParser` to parse and build a query string according to RFC3986 rules.
- `League\Uri\Schemes\Generic\AbstractUri` to enable better URI extension
- URI Modifiers classes to modify URI objects in an uniform way for interoperability
- A `Data` class to specifically manipulate `data` schemed URI
- A `Http` class to specifically manipulate `http`,`https` schemed URI
- A `Ftp` class to specifically manipulate `ftp` schemed URI
- A `Ws` class to specifically manipulate `ws`, `wss` schemed URI
- A `DataPath` component class to manipulate Data-uri path component
- A `HierarchicalPath` to manipulate Hierarchical-like path component
- Support for IP host

### Fixed

- Move namespace from `League\Url` to `League\Uri` to avoid dependency hell
- Uri components classes are fixed to comply to `RFC3986`
- Uri components classes are now all immutable value objects

### Deprecated

- Nothing

### Remove

- Support for `PHP 5.4` and `PHP 5.3`
- Dependency on PHP `parse_url`, `parse_str` and `http_build_query` functions
- Dependency on the `True/php-punycode` library
- `League\Url\Url`, `League\Url\UrlImmutable`, `League\Url\UrlConstants` classes
- Most of the public API is removed