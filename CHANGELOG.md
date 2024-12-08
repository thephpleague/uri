# Changelog

All Notable changes to `League\Uri` will be documented in this file

## [7.5.0](https://github.com/thephpleague/uri/compare/7.4.1...7.5.0) - 2024-12-08

### Added

- `Uri::getUsername` returns the encoded user component of the URI.
- `Uri::getPassword` returns the encoded password component of the URI.
- `BaseUri::isOpaque` tells whether a URI is opaque.
- Using PHP8.4 `Deprecated` attribute to signal deprecated public API methods and constants.

### Fixed

- Improve PSR-7 `Http` class implementation.
- `BaseUri::from` will compress the IPv6 host to its compressed form if possible.

### Deprecated

- Usage of PSR-7 `UriFactoryInterface` is deprecated in `BaseUri` class

### Removed

- None

## [7.4.1](https://github.com/thephpleague/uri/compare/7.4.0...7.4.1) - 2024-02-23

### Added

- None

### Fixed

- Fix package to avoid PHP8.4 deprecation warnings

### Deprecated

- None

### Removed

- None

## [7.4.0](https://github.com/thephpleague/uri/compare/7.3.0...7.4.0) - 2023-12-01

### Added

- `Uri::fromData`
- `Uri::fromRfc8089`
- `BaseUri::unixPath`
- `BaseUri::windowsPath`
- `BaseUri::toRfc8089`

### Fixed

- None

### Deprecated

- None

### Removed

- None

## [7.3.0](https://github.com/thephpleague/uri/compare/7.2.1...7.3.0) - 2023-09-09

### Added

- None

### Fixed

- URI Template incorrect when variable name only contains numbers [#109](https://github.com/thephpleague/uri-src/issues/119) by [GrahamCampbell](https://github.com/GrahamCampbell)
- Exception message typo fix [#121](https://github.com/thephpleague/uri-src/pull/121) by [GrahamCampbell](https://github.com/GrahamCampbell)

### Deprecated

- None

### Removed

- None

## [7.2.1](https://github.com/thephpleague/uri-components/compare/7.2.0...7.2.1) - 2023-08-30

### Added

- None

### Fixed

- `composer.json` constraints

### Deprecated

- None

### Removed

- None

## [7.2.0](https://github.com/thephpleague/uri/compare/7.1.0...7.2.0) - 2023-08-30

### Added

- `BasUri::hasIDN`

### Fixed

- None

### Deprecated

- None

### Removed

- None

## [7.1.0](https://github.com/thephpleague/uri/compare/7.0.0...7.1.0) - 2023-08-21

### Added

- None

### Fixed

- Using the `Encoder` class to normalize encoding and decoding in all packages

### Deprecated

- None

### Removed

- None

## [7.0.0](https://github.com/thephpleague/uri/compare/6.8.0...7.0.0) - 2023-08-10

### Added

- `League\Uri\Uri::new`
- `League\Uri\Uri::fromComponents`
- `League\Uri\Uri::fromServer`
- `League\Uri\Uri::fromWindowsPath`
- `League\Uri\Uri::fromUnixPath`
- `League\Uri\Uri::fromFileContents`
- `League\Uri\Uri::fromClient`
- `League\Uri\Uri::fromTemplate`
- `League\Uri\Http::new`
- `League\Uri\Http::fromComponents`
- `League\Uri\Http::fromBaseUri`
- `League\Uri\Http::fromServer`
- `League\Uri\Http::fromTemplate`
- `League\Uri\UriTemplate::expandOrFail`
- `League\Uri\UriTemplate\Template::expandOrFail`
- `League\Uri\UriTemplate\TemplateCanNotBeExpanded`
- `League\Uri\UriString::parseAuthority`
- `League\Uri\UriString::buildAuthority`
- `League\Uri\BaseUri`

### Fixed

- `League\Uri\UriInfo` uri input now supports `Stringable` and `string` type.
- `League\Uri\UriTemplate\VariableBag` implements the `IteratorAggregate` interface
- `League\Uri\UriTemplate\Operator` to improve internal representation when using UriTemplate features.

### Deprecated

- `League\Uri\UriResolver` use `League\Uri\BaseUri` instead
- `League\Uri\Uri::createFromString` use `League\Uri\Uri::new`
- `League\Uri\Uri::createFromUri` use `League\Uri\Uri::new`
- `League\Uri\Uri::createFromComponents` use `League\Uri\Uri::fromComponents`
- `League\Uri\Uri::createFromBaseUri` use `League\Uri\Uri::fromBaseUri`
- `League\Uri\Uri::createFromServer` use `League\Uri\Uri::fromServer`
- `League\Uri\Uri::createFromWindowsPath` use `League\Uri\Uri::fromWindowsPath`
- `League\Uri\Uri::createFromUnixPath` use `League\Uri\Uri::fromUnixPath`
- `League\Uri\Uri::createFromDataPath` use `League\Uri\Uri::fromFileContents`
- `League\Uri\Http::createFromString` use `League\Uri\Http::new`
- `League\Uri\Http::createFromUri` use `League\Uri\Http::new`
- `League\Uri\Http::createFromComponents` use `League\Uri\Http::fromComponents`
- `League\Uri\Http::createFromBaseUri` use `League\Uri\Http::fromBaseUri`
- `League\Uri\Http::createFromServer` use `League\Uri\Http::fromServer`
- `League\Uri\UriTemplate\Template::createFromString` use `League\Uri\UriTemplate\Template::new`

### Remove

- Support for `__set_state`
- Support for `__debugInfo`
- `League\Uri\UriTemplate\VariableBag::all`
- `League\Uri\Exceptions\TemplateCanNotBeExpanded` use `League\Uri\UriTemplate\TemplateCanNotBeExpanded` instead
- `League\Uri\UriString` class. Class moved to the `uri-interfaces` package.
- 
## [6.8.0](https://github.com/thephpleague/uri/compare/6.7.2...6.8.0) - 2022-09-13

### Added

- Added PHP8.2+ `SensitiveParameter` attributes to user information component

### Fixed

- Optimize URI performance for server intensive usage [206](https://github.com/thephpleague/uri/pull/206) by [@kelunik](https://github.com/kelunik)
- Improve `Template` resolution
- Added PHPBench to benchmark the package main functionnalities.
- Normalize `UriInterface::getPath` value in the context of multiple leading slash characters.

### Deprecated

- None

### Remove

- Support for PHP7.4 and PHP8.0

## [6.7.2](https://github.com/thephpleague/uri/compare/6.7.1...6.7.2) - 2022-09-13

### Added

- None

### Fixed

- `Http::getPath` and `Uri::getPath` methods returned values are normalized to prevent potential XSS and open redirect vectors.

### Deprecated

- None

### Remove

- None

## [6.7.1](https://github.com/thephpleague/uri/compare/6.7.0...6.7.1) - 2022-06-29

### Added

- None

### Fixed

- `UriInfo::isCrossOrigin` method is fix to make it work with any PSR-7 compliant object [205](https://github.com/thephpleague/uri/pull/205)

### Deprecated

- None

### Remove

- None

## [6.7.0](https://github.com/thephpleague/uri/compare/6.6.0...6.7.0) - 2022-06-28

### Added

- `UriInfo::isCrossOrigin` method

### Fixed

- None

### Deprecated

- None

### Remove

- None

## [6.6.0](https://github.com/thephpleague/uri/compare/6.5.0...6.6.0) - 2022-05-28

### Added

- None

### Fixed

- Some errors are moved from `TypeError` to `InvalidArgumentException` to align with other `UriInterface` PSR-7 implementations.
- Improved documentation by [@GwendolenLynch](https://github.com/GwendolenLynch)
- Added PSR7 compliance tests from [the PHP-HTTP group](https://github.com/php-http/psr7-integration-tests)

### Deprecated

- None

### Remove

- Support for PHP7.3

## 6.5.0 - 2021-08-27

### Added

- `Uri::toString` as a clean method to return URI string representation.
- `IDNA` conversion in now normalize using the `Uri-Interface` package classes

### Fixed

- conversion host component from ASCII to unicode no longer throw

### Deprecated

- None

### Remove

- Support for PHP7.2

## 6.4.0 - 2020-11-23

### Added

- `HttpFactory` a class that implements PSR-17 UriFactoryInterface. The package needs to be present for the class to work.

### Fixed

- Bugfix `Uri::formatPath` to improve URL encoding in the path component [#180](https://github.com/thephpleague/uri/pull/180) thanks [mdawaffe](https://github.com/mdawaffe).

### Deprecated

- Nothing

### Remove

- None

## 6.3.0 - 2020-08-13

### Added 

- `UriInfo::getOrigin` to returns the URI origin as described in the WHATWG URL Living standard specification
- `UriTemplate\Template`, `UriTemplate\Expression`, `UriTemplate\VarSpecifier`, `UriTemplate\VariableBag` to 
improve `UriTemplate` implementation.
- Added early support for PHP8

### Fixed

- `UriTemplate` complete rewrite by reducing deep nested array usage.
- Exception misleading message see issue [#167](https://github.com/thephpleague/uri/issues/167)
- `Uri::withScheme` Uri validation failed to catch the empty string as an invalid scheme. [#171](https://github.com/thephpleague/uri/issues/171)

### Deprecated

- Nothing

### Remove

- None

## 6.2.1 - 2020-03-17

### Added 

- None

### Fixed

- Bugfix `UriTemplate::expand` to comply with expansion rules for undefined variables [#161](https://github.com/thephpleague/uri/pull/161) thanks [Gabe Sullice](https://github.com/gabesullice)
- Improve package testing settings and environment.

### Deprecated

- Nothing

### Remove

- None

## 6.2.0 - 2020-02-08

### Added 

- None

### Fixed

- None

### Deprecated

- Nothing

### Remove

- Hard dependencies on the `ext-mbstring` and the `ext-fileinfo` PHP extensions [#154](https://github.com/thephpleague/uri/pull/154) thanks [Nicolas Grekas](https://github.com/nicolas-grekas)

## 6.1.1 - 2020-01-30

### Added 

- Nothing

### Fixed

- `League\Uri\UriTemplate` variables validation and normalization improved

### Deprecated

- Nothing

### Remove

- Nothing

## 6.1.0 - 2020-01-29

### Added 

- `League\Uri\UriTemplate` a class to handle uri template expansion as described in RFC7560 see PR [#153](https://github.com/thephpleague/uri/pull/153)

### Fixed

- improving `idn_to_ascii` usage see [#150](https://github.com/thephpleague/uri/issues/150) thanks to [ntzm](https://github.com/ntzm)

### Deprecated

- Nothing

### Remove

- Nothing

## 6.0.1 - 2019-11-23

### Added 

- Nothing

### Fixed

- `Uri` should not depend on `intl` extension if the host is in its ascii form [#141](https://github.com/thephpleague/uri/issues/141)

### Deprecated

- Nothing

### Remove

- Nothing

## 6.0.0 - 2019-10-18

### Added

- `League\Uri\UriInfo`: to get RFC3986 information from an URI object
- `League\Uri\UriResolver`: to resolve or relativize an URI object
- `League\Uri\UriString`: to parse or build an URL into or from its components
- `League\Uri\Uri::createFromBaseUri` named constructor
- `League\Uri\Uri::createFromDataPath` named constructor
- `League\Uri\Uri::createFromPsr7` named constructor
- `League\Uri\Uri::createFromUnixPath` named constructor
- `League\Uri\Uri::createFromWindowsPath` named constructor
- `League\Uri\Http::createFromBaseUri` named constructor

### Fixed

- Improve parsing and building URI
- All URI object are now finals and supports parameter type widening
- `League\Uri\Uri` implements the `JsonSerializable` interface
- `League\Uri\Http` implements the `JsonSerializable` interface

### Deprecated

- None

### Remove

- support for PHP7.1 and PHP7.0
- `create` function defined in the  `League\Uri` namespace replaced by `League\Uri\Uri::createFromBaseUri`
- `League\Uri\Factory` replaced by `League\Uri\Uri`
- `League\Uri\Data` replaced by `League\Uri\Uri`
- `League\Uri\File` replaced by `League\Uri\Uri`
- `League\Uri\Ftp` replaced by `League\Uri\Uri`
- `League\Uri\Ws` replaced by `League\Uri\Uri`
- `League\Uri\UriException` replaced by `League\Uri\Contract\UriException`
- `League\Uri\AbstractUri` internal, replaced by `League\Uri\Uri`
- `League\Uri\Schemes` namespace and all classes inside
- `League\Uri\Uri` no longer implements `League\Uri\UriInterface`

## 5.3.0 - 2018-03-14

See packages release notes for more informations

- [URI Parser](https://github.com/thephpleague/uri-parser/releases/tag/1.4.0)
- [URI Components](https://github.com/thephpleague/uri-components/releases/tag/1.8.0)
- [URI Hostname Parser](https://github.com/thephpleague/uri-hostname-parser/releases/tag/1.1.0)
- [URI Manipulations](https://github.com/thephpleague/uri-manipulations/releases/tag/1.5.0)
- [URI Schemes](https://github.com/thephpleague/uri-schemes/releases/tag/1.2.0)

### Added

- IPvFuture support

### Fixed

- Adding PHPStan
- Improve RFC3986 compliance
- Improve performance

### Remove

- remove `mbstring` extension requirement

## 5.2.0 - 2017-12-01

- [URI Parser](https://github.com/thephpleague/uri-parser/releases/tag/1.3.0)
- [URI Hostname parser](https://github.com/thephpleague/uri-hostname-parser/releases/tag/1.0.4)
- [URI Manipulations 1.3.0 Changelog](https://github.com/thephpleague/uri-manipulations/releases/tag/1.3.0)
- [URI Components 1.5.0 Changelog](https://github.com/thephpleague/uri-components/releases/tag/1.5.0)
- [URI Schemes 1.1.1 Changelog](https://github.com/thephpleague/uri-schemes/releases/tag/1.1.1)

### Fixed

- Support for PHP7.2

## 5.1.0 - 2017-11-17

### Added

- Support for PHP7.2

### Fixed

- Update library dependencies

### Deprecated

- Nothing

### Remove

- Nothing

## 5.0.0 - 2017-02-06

### Added

The library is now a metapackage, you can read the [migration guide](https://uri.thephpleague.com/uri/5.0/upgrading/) for upgrading or the complete [documentation for the new version](https://uri.thephpleague.com/uri/5.0/).

### Remove

- PHP5 support

## 4.2.2 - 2016-12-12

### Added

- Nothing

### Fixed

- issue [#91](https://github.com/thephpleague/uri/issues/91) Path modifier must be RFC3986 compliant
- issue [#94](https://github.com/thephpleague/uri/issues/91) Improve Query parser encoder
- `Formatter::__invoke` path must be RFC3986 compliant

### Deprecated

- Nothing

### Remove

- Nothing

## 4.2.1 - 2016-11-24

### Added

- Nothing

### Fixed

- issue [#84](https://github.com/thephpleague/uri/issues/84)

### Deprecated

- Nothing

### Remove

- Nothing

## 4.2.0 - 2016-09-30

### Added

- `Component::getContent` returns the raw encoded representation of a component
- `Component::withContent` to create a new instance from a given raw encoded content
- `getDecoded` method to access the decoded content for the following classes:
    - `User`
    - `Pass`
    - `Fragment`
- Support for PHP's magic methods `__debugInfo` and `__set_state` to improve debugging
- `Modifiers\Relativize`
- `Modifiers\DecodeUnreservedCharacters`
- `Path::createFromSegments`
- `Path::getSegments`
- `Host::createFromLabels`
- `Host::getLabels`
- `Query::createFromPairs`
- `Query::getPairs`
- `Modifiers\uri_reference` function to return URI reference state.

### Fixed

- Components encoding/decoding to be more RFC3986 compliant
- `Host::getRegisterableDomain` must always return a string as per the host interface expected return type
- `Host::getSubdomain` must always return a string as per the host interface expected return type
- `Host::isPublicSuffixValid` when no `publicSuffix` information is found
- `Host::isPublicSuffixValid` must always return a string as per the host interface expected return type
- On instantiation, query and fragment delimiter are preserved
- `createFromComponents` failing with `parse_url` result when no path is defined
- On URI transformation `InvalidArgumentException` exceptions are emitted instead of `RuntimeException` ones to normalize exception to PSR-7
- `Modifiers\Normalize` class removes dot segments only on URI with absolute path.
- `Modifiers\Normalize` class decode all unreserved characters.
- `Ftp` and `Ws` objects now accept relative reference URI without the scheme.

### Deprecated

- `Component::modify` use `Component::withContent` instead
- `Host::getLiteral`
- `Port::toInt` use `Port::getContent` instead
- `HierarchicalPath::createFromArray` use `HierarchicalPath::createFromSegments` instead
- `HierarchicalPath::toArray` use `HierarchicalPath::getSegments` instead
- `Host::createFromArray` use `Host::createFromLabels` instead
- `Host::toArray` use `Host::getLabels` instead
- `Query::createFromArray` use `Query::createFromPairs` instead
- `Query::toArray` use `Query::getPairs` instead
- `UriPart::sameValueAs`

### Remove

- Nothing

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
