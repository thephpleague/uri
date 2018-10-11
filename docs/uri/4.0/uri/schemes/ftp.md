---
layout: default
title: Ftp URIs
redirect_from:
    - /4.0/uri/schemes/ftp/
---

# Ftp URI

To ease working with FTP URIs, the library comes bundle with a URI specific FTP class `League\Uri\Schemes\Ftp`.

## Validation

A FTP URI must contain the `ftp` scheme. It can not contains a query and or a fragment component.

<p class="message-notice">Adding contents to the fragment or query components throws an <code>InvalidArgumentException</code> exception</p>

~~~php
<?php

use League\Uri\Schemes\Ftp as FtpUri;

$uri = FtpUri::createFromString('ftp://thephpleague.com/path/to/image.png;type=i');
$uri->withQuery('p=1'); //throw an InvalidArgumentException - a query component was given
~~~

<p class="message-notice">Starting with version <code>4.2</code> schemeless FTP Uri will no longer trigger an <code>InvalidArgumentException</code> exception</p>

Apart from the fragment and the query components, the Ftp URIs share the same [host validation limitation](/uri/4.0/uri/schemes/http/#validation) as Http URIs.

## Properties

The FTP URI class uses the specialized [HierarchicalPath](/uri/4.0/components/hierarchical-path/) class to represents its path. using PHP's magic `__get` method you can access the object path and get more informations about the underlying path.

~~~php
<?php

use League\Uri\Schemes\Ftp as FtpUri;

$uri = FtpUri::createFromString('ftp://thephpleague.com/path/to/image.png;type=i');
echo $uri->path->getBasename();  //display 'image.png;type=i'
echo $uri->path->getDirname();   //display '/path/to'
echo $uri->path->getExtension(); //display 'png'
$uri->path->getSegments(); //returns an array representation of the path segments
~~~