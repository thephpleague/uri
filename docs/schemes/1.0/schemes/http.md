---
layout: default
title: Http URIs
redirect_from:
    - /5.0/uri/schemes/http/
---

# Http, Https URI

<p class="message-warning">Starting with version <code>1.1.0</code> all URI objects are defined in the <code>League\Uri</code> namespace. The <code>League\Uri\Schemes</code> namespace is deprecated and will be removed in the next major release.</p>

## Instantiation

To work with Http URIs you can use the `League\Uri\Http` class. This class handles secure and insecure Http URI. In addition to the default named constructors, the `Http` class can be instantiated using the server variables.

~~~php
<?php

use League\Uri;

//don't forget to provide the $_SERVER array
$uri = Uri\Http::createFromServer($_SERVER);
~~~

<p class="message-warning">The method only relies on the server's safe parameters to determine the current URI. If you are using the library behind a proxy the result may differ from your expectation as no <code>$_SERVER['HTTP_X_*']</code> header is taken into account for security reasons.</p>

## Validation

The scheme of a HTTP(s) URI must be equal to `http`, `https` or be equal to `null`.

### Authority presence

If a scheme is present and the scheme specific part of a Http URI is not empty the URI can not contain an empty authority. Thus, some Http URI modifications must be applied in a specific order to preserve the URI validation.

~~~php
<?php

use League\Uri;

$uri = Uri\Http::createFromString('http://uri.thephpleague.com/');
echo $uri->withHost('')->withScheme('');
// will throw an League\Uri\UriException
// you can not remove the Host if a scheme is present
~~~

Instead you are required to proceed as below

~~~php
<?php

use League\Uri;

$uri = Uri\Http::createFromString('http://uri.thephpleague.com/');
echo $uri->withScheme('')->withHost(''); //displays "/"
~~~

<p class="message-notice">When an invalid URI object is created an <code>UriException</code> exception is thrown</p>


### Path validity

According to RFC3986, if an HTTP URI contains a non empty authority part, the URI path must be the empty string or absolute. Thus, some modification may trigger an <code>UriException</code>.

~~~php
<?php

use League\Uri;

$uri = Uri\Http::createFromString('http://uri.thephpleague.com/');
echo $uri->withPath('uri/schemes/http');
// will throw an League\Uri\UriException
~~~

Instead you are required to submit a absolute path

~~~php
<?php

use League\Uri;

$uri = Uri\Http::createFromString('http://uri.thephpleague.com/');
echo $uri->withPath('/uri/schemes/http'); // displays 'http://uri.thephpleague.com/uri/schemes/http'
~~~

Of note this does not mean that rootless path are forbidden, the following code is fine.

~~~php
<?php

use League\Uri;

$uri = Uri\Http::createFromString('?foo=bar');
echo $uri->withPath('uri/schemes/http'); // displays 'uri/schemes/http?foo=bar'
~~~

## Relation with PSR-7

The `Http` class implements the PSR-7 `UriInterface` interface. This means that you can use this class anytime you need a PSR-7 compliant URI object.