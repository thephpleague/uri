---
layout: default
title: File URIs
---

# File URI

To ease working with File URIs, the library comes bundle with a URI specific File class.

## Instantiation

In addition to the [defined named constructors](/dev-master/uri/#instantiation), because file path depends on the underlying OS, you can also instantiate a new File URI object from a file path using:

- the `createFromUnixPath` named constructor
- the `createFromWindowsPath` named constructor

~~~php
<?php

use League\Uri\Schemes\File as FileUri;

$uri = FileUri::createFromWidowsPath(c:\windows\My Documents\my word.docx);
echo $uri; //returns 'file:///c:My%20Documents/my%20word.docx'
~~~

## Validation

Even though all URI properties are defined and accessible attempt to set any component other than the scheme, the host, and the path will result in the object throwing a `InvalidArgumentException` exception. As adding content to theses URI parts will generate an invalid File URI.

~~~php
<?php

use League\Uri\Schemes\File as FileUri;

$uri = DataUri::createFromUnixPath('/path/./../relative');
$uri->withQuery('foo=bar'); //thrown an InvalidArgumentException
~~~