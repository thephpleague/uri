---
layout: default
title: Data URIs
redirect_from:
    - /5.0/uri/schemes/data/
---

# Data URI

<p class="message-warning">Starting with version <code>1.1.0</code> all URI objects are defined in the <code>League\Uri</code> namespace. The <code>League\Uri\Schemes</code> namespace is deprecated and will be removed in the next major release.</p>

To ease working with Data URIs, the library comes bundle with a URI specific Data class. This class follows [RFC2397](http://tools.ietf.org/html/rfc2397)

## Instantiation

In addition to the defined named constructors, because data URI represents files, you can also instantiate a new data URI object from a file path using the `createFromPath` named constructor

~~~php
<?php

use League\Uri;

$uri = Uri\Data::createFromPath('path/to/my/png/image.png');
echo $uri; //returns 'data:image/png;charset=binary;base64,...'
//where '...' represent the base64 representation of the file
~~~

If the file is not readable or accessible an `UriException` exception will be thrown. The class uses PHP's `finfo` class to detect the required mediatype as defined in RFC2045.

## Validation

Even though all URI properties are defined and accessible attempt to set any component other than the path will result in the object throwing a `UriException` exception. As adding data to theses URI parts will generate an invalid Data URI.

~~~php
<?php

use League\Uri;

$uri = Uri\Data::createFromPath('path/to/my/png/image.png');
$uri->getHost(); //return '' an empty string
$uri->withHost('example.com'); // will throw an League\Uri\UriException
~~~