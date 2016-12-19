---
layout: default
title: URI formatter and URI middleware
---

URI manipulations
=======

[![Build Status](https://img.shields.io/travis/thephpleague/uri-manipulations/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/uri-manipulations)
[![Latest Version](https://img.shields.io/github/release/thephpleague/uri-manipulations.svg?style=flat-square)](https://github.com/thephpleague/uri-manipulations/releases)

The `League Uri Manipulations` repository contains:

- an URI formatter to format URI string representation output;
- a function `uri_reference` to get the URI object reference information according to RFC3986;
- URI middlewares to filter Uri objects;

To be used, the URI objects are required to implement one of the following interface:

- `League\Uri\Interfaces\Uri`;
- `Psr\Http\Message\UriInteface`;

All functions and classes are located under the following namespace : `League\Uri\Modifiers`
