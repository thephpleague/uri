---
layout: default
title: URI components
---

Uri Components
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-components)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-components.svg?style=flat-square)](https://github.com/thephpleague/uri-components/releases)

This package contains concrete URI components object represented as immutable value object. Each URI component object implements the `League\Uri\Components\ComponentInterface` interface [the methods](/dev-master/components/api/).

All URI components objects are located under the following namespace : `League\Uri\Components`


List of Component objects
--------

The following URI component objects are defined (order alphabetically):

- [DataPath](/dev-master/components/data/) : the Data Path component [RFC 2397](https://tools.ietf.org/html/rfc2397)
- [HierarchicalPath](/dev-master/components/hierarchicalpath/) : the hierarchical Path component [RFC 3986](https://tools.ietf.org/html/rfc3986)
- [Host](/dev-master/components/host/) : the Host component
- `Fragment` : the Fragment component
- [Path](/dev-master/components/path/) : the generic Path component
- `Port` : the Port component
- [Query](/dev-master/components/query/) : the Query component
- `Scheme` : the Scheme component
- [UserInfo](/dev-master/components/userinfo/) : the User Info component

Some components exposes more methods to enable better manipulations.