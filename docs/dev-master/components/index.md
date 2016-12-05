---
layout: default
title: URI components
---

Uri Components
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-components)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-components.svg?style=flat-square)](https://github.com/thephpleague/uri-components/releases)

This package contains concrete URI components object represented as immutable value object. Each URI component object implements [the methods](/dev-master/components/api/) `League\Uri\Interfaces\Component` interface as defined in the [uri-interfaces package](https://github.com/thephpleague/uri-interfaces).


List of Component objects
--------

The following URI component objects are defined:

- `League\Uri\Components\Scheme` : the Scheme component
- `League\Uri\Components\Port` : the Port component
- `League\Uri\Components\Fragment` : the Fragment component
- [League\Uri\Components\UserInfo](/dev-master/components/userinfo/) : the User Info component
- [League\Uri\Components\Query](/dev-master/components/query/) : the Query component
- [League\Uri\Components\Host](/dev-master/components/host/) : the Host component
- [League\Uri\Components\Path](/dev-master/components/path/) : the generic Path component
- [League\Uri\Components\HierarchicalPath](/dev-master/components/hierarchicalpath/) : the hierarchical Path component [RFC 3986](https://tools.ietf.org/html/rfc3986)
- [League\Uri\Components\DataPath](/dev-master/components/data/) : the Data Path component [RFC 2397](https://tools.ietf.org/html/rfc2397)

Some components exposes more methods to enable better manipulations.