---
layout: default
title: Data URIs
redirect_from:
    - /4.0/uri/schemes/data-uri/
---

# Data URI

To ease working with Data URIs, the library comes bundle with a URI specific Data class. This class follows [RFC2397](http://tools.ietf.org/html/rfc2397)

## Instantiation

In addition to the [defined named constructors](/uri/4.0/uri/instantiation/#uri-instantiation), because data URI represents files, you can also instantiate a new data URI object from a file path using the `createFromPath` named constructor

~~~php
<?php

use League\Uri\Schemes\Data as DataUri;

$uri = DataUri::createFromPath('path/to/my/png/image.png');
echo $uri; //returns 'data:image/png;charset=binary;base64,...'
//where '...' represent the base64 representation of the file
~~~

If the file is not readable or accessible an `RuntimeException` exception will be thrown. The class uses PHP's `finfo` class to detect the required mediatype as defined in RFC2045.

## Validation

Even though all URI properties are defined and accessible attempt to set any component other than the path will result in the object throwing a `InvalidArgumentException` exception. As adding data to theses URI parts will generate an invalid Data URI.

~~~php
<?php

use League\Uri\Schemes\Data as DataUri;

$uri = DataUri::createFromPath('path/to/my/png/image.png');
$uri->getHost(); //return '' an empty string
$uri->withHost('example.com'); //thrown an InvalidArgumentException
~~~

## Properties

The data URI class uses the [DataPath](/uri/4.0/components/datauri-path/) class to represents its path. using PHP's magic `__get` method you can access the object path and get more informations about the underlying file.

~~~php
<?php

use League\Uri\Schemes\Data as DataUri;

$uriString = 'data:text/plain;charset=us-ascii,Hello%20World%21';
$uri = DataUri::createFromString($uriString);
echo $uri->path->getMediaType(); //display 'text/plain;charset=us-ascii'
echo $uri->path->getMimeType(); //display 'text/plain'
echo $uri->path->getParameters(); //display 'charset=us-ascii'
echo $uri->path->getData(); //display 'Hello%20World%21'
$uri->path->isBinaryData(); //returns false
$fileObject = $uri->path->save('/path/where/to/save/data', 'w');
//$fileObject is a \SplFileObject reference
~~~