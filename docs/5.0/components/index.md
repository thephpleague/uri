---
layout: default
title: URI components
---

Uri Components
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-components)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-components.svg?style=flat-square)](https://github.com/thephpleague/uri-components/releases)

This package contains concrete URI components object represented as immutable value object as well as function to ease component parsing.


List of URI component objects
--------

Each URI component object implements the `League\Uri\Components\ComponentInterface` interface.

All URI components objects are located under the following namespace : `League\Uri\Components`


The following URI component objects are defined (order alphabetically):

- [DataPath](/5.0/components/data-path/) : the Data Path component
- [HierarchicalPath](/5.0/components/hierarchical-path/) : the hierarchical Path component
- [Host](/5.0/components/host/) : the Host component
- [Fragment](/5.0/components/fragment/) : the Fragment component
- [Path](/5.0/components/path/) : the generic Path component
- [Port](/5.0/components/port/) : the Port component
- [Query](/5.0/components/query/) : the Query component
- [Scheme](/5.0/components/scheme/) : the Scheme component
- [UserInfo](/5.0/components/userinfo/) : the User Info component


List of URI component function
--------

- [parse_query](/5.0/components/functions) : to replace and improve PHP's `parse_str` function