---
layout: default
title: Ftp URIs
---

# Ftp URI

To ease working with FTP URIs, the library comes bundle with a URI specific FTP class `League\Uri\Schemes\Ftp`.

## Validation

The scheme of a FTP URI must be equal to `ftp` or be undefined. It can not contains a query and or a fragment component.

<p class="message-notice">Adding contents to the fragment or query components throws an <code>InvalidArgumentException</code> exception</p>

~~~php
<?php

use League\Uri\Schemes\Ftp as FtpUri;

$uri = FtpUri::createFromString('ftp://thephpleague.com/path/to/image.png;type=i');
$uri->withQuery('p=1'); //throw an InvalidArgumentException - a query component was given
~~~

Apart from the fragment and the query components, the Ftp URIs share the same [host validation limitation](/5.0/uri/schemes/http/#validation) as Http URIs.