---
layout: default
title: Ftp URIs
redirect_from:
    - /5.0/uri/schemes/ftp/
---

# Ftp URI

<p class="message-warning">Starting with version <code>1.1.0</code> all URI objects are defined in the <code>League\Uri</code> namespace. The <code>League\Uri\Schemes</code> namespace is deprecated and will be removed in the next major release.</p>

To ease working with FTP URIs, the library comes bundle with a URI specific FTP class `League\Uri\Ftp`.

## Validation

The scheme of a FTP URI must be equal to `ftp` or be undefined. It can not contains a query and or a fragment component.

<p class="message-notice">Adding contents to the fragment or query components throws an <code>UriException</code> exception</p>

~~~php
<?php

use League\Uri;

$uri = Uri\Ftp::createFromString('ftp://thephpleague.com/path/to/image.png;type=i');
$uri->withQuery('p=1'); // will throw an League\Uri\UriException
~~~

Apart from the fragment,  the query components and the scheme definition, the FTP URIs share the same [validation rules](/5.0/uri/schemes/http/#validation) as Http URIs.