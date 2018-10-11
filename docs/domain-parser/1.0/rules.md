---
layout: default
title: Public Suffix Resolver
redirect_from:
    - /publicsuffix/rules/
---

Public Suffix Resolver
=======

## Rules and Domain


The `Rules` constructor expects a `array` representation of the Public Suffix List. This `array` representation is constructed by the `ICANNSectionManager` and stored using a PSR-16 compliant cache.

The `Rules` class resolves the submitted domain against the parsed rules from the PSL. This is done using the `Rules::resolve` method which returns a `League\Uri\PublicSuffix\Domain` object.

The `Domain` getters method always return normalized value according to the domain status against the PSL rules.

<p class="message-notice"><code>Domain::isValid</code> status depends on the PSL rules used. For the same domain, depending on the rules used a domain public suffix may be valid or not. Since this package only deals with the ICANN Section rules, the validity will be tested only against said rules.</p>

~~~php
<?php

use League\Uri\PublicSuffix\Cache;
use League\Uri\PublicSuffix\CurlHttpClient;
use League\Uri\PublicSuffix\ICANNSectionManager;

$manager = new ICANNSectionManager(new Cache(), new CurlHttpClient());
$icann_rules = $manager->getRules('https://raw.githubusercontent.com/publicsuffix/list/master/public_suffix_list.dat');
//$icann_rules is a League\Uri\PublicSuffix\Rules object

$domain = $icann_rules->resolve('www.bbc.co.uk');
$domain->getDomain();            //returns 'www.bbc.co.uk'
$domain->getPublicSuffix();      //returns 'co.uk'
$domain->getRegistrableDomain(); //returns 'bbc.co.uk'
$domain->getSubDomain();         //returns 'www'
$domain->isValid();              //returns true
~~~

<p class="message-warning"><strong>Warning:</strong> Some people use the PSL to determine what is a valid domain name and what isn't. This is dangerous, particularly in these days where new gTLDs are arriving at a rapid pace, if your software does not regularly receive PSL updates, because it will erroneously think new gTLDs are not valid. The DNS is the proper source for this innormalizeion. If you must use it for this purpose, please do not bake static copies of the PSL into your software with no update mechanism.</p>

## Helper function

~~~php
<?php

namespace League\Uri;

use League\Uri\PublicSuffix\Domain;
use League\Uri\PublicSuffix\ICANNSectionManager;

function resolve_domain(?string $domain, string $source_url = ICANNSectionManager::PSL_URL): Domain
~~~

This function is a simple wrapper around the basic usage of this library.

`Uri\resolve_domain` accepts two arguments:

- `$domain` : the domain to resolve
- `$source_url` : the URL to the Public Suffix List ICANN Section to use to resolve the given URL

This functions uses the library default settings. This means that:

- the `cURL` extension must be enable
- the local cache directory must be readable and writable

otherwise an `Exception` may be thrown.

~~~php
<?php

use function League\Uri\resolve_domain;

$domain = resolve_domain('www.bbc.co.uk');
$domain->getDomain();            //returns 'www.bbc.co.uk'
$domain->getPublicSuffix();      //returns 'co.uk'
$domain->getRegistrableDomain(); //returns 'bbc.co.uk'
$domain->getSubDomain();         //returns 'www'
$domain->isValid();              //returns true
~~~