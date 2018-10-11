---
layout: default
title: File URIs
redirect_from:
    - /5.0/uri/schemes/file/
---

# File URI

<p class="message-warning">Starting with version <code>1.1.0</code> all URI objects are defined in the <code>League\Uri</code> namespace. The <code>League\Uri\Schemes</code> namespace is deprecated and will be removed in the next major release.</p>

To ease working with File URIs, the library comes bundle with a URI specific File class.

## Instantiation

In addition to the defined named constructors, because file path depends on the underlying OS, you can also instantiate a new File URI object from a file path using:

- the `createFromUnixPath` named constructor
- the `createFromWindowsPath` named constructor

~~~php
<?php

use League\Uri;

$uri = Uri\File::createFromWidowsPath('c:\windows\My Documents\my word.docx');
echo $uri; //returns 'file://localhost/c:My%20Documents/my%20word.docx'
~~~

## Validation

Even though all URI properties are defined and accessible attempt to set any component other than the scheme, the host, and the path will result in the object throwing a `InvalidArgumentException` exception. As adding content to theses URI parts will generate an invalid File URI.

~~~php
<?php

use League\Uri;

$uri = Uri\File::createFromUnixPath('/path/./../relative');
$uri->withQuery('foo=bar'); // will throw an League\Uri\UriException
~~~

## URI normalization

If the host file is the empty string it will be converted to `localhost`.

~~~php
<?php

use League\Uri;

$uri = Uri\File::createFromString('file:///path/to/file.csv');
echo $uri; //display file://localhost/path/to/file.csv
~~~
