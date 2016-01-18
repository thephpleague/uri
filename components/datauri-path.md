---
layout: default
title: The Data Uri Path component
---

# Data URI Path

The library provides a `League\Uri\Components\DataPath` class to ease complex path manipulation on a Data URI object.  The class extends the default `Path` object with methods dedicated to manipulate Data URI paths.

## Instantiation

### Using the default constructor

Just like any other component, a new `League\Uri\Components\DataPath` object can be instantiated using its default constructor.

~~~php
use League\Uri\Components\DataPath as Path;

$path = new Path('text/plain;charset=us-ascii,Hello%20World%21');
echo $path; //returns 'text/plain;charset=us-ascii,Hello%20World%21'
~~~

<p class="message-notice">The <code>mediatype</code> is only validated according to its syntax. You should use an independent mediatype validator if necessary.</p>

<p class="message-warning">If the submitted value is not a valid path an <code>InvalidArgumentException</code> will be thrown.</p>

### Using a Data Uri object

~~~php
use League\Uri\Schemes\Data as DataUri;

$uri  = DataUri::createFromString('data:text/plain;charset=us-ascii,Hello%20World%21');
$path = $uri->path; // $path is a League\Uri\Components\DataPath object;
~~~

### Using a named constructor

### Instantiating using a file path

Because data URI represents files you can also instantiate a new data URI object from a file path using the `createFromPath` named constructor

~~~php
use League\Uri\Components\DataPath as Path;

$path = Path::createFromPath('path/to/my/png/image.png');
echo $uri; //returns 'image/png;charset=binary;base64,...'
//where '...' represent the base64 representation of the file
~~~

If the file is not readable or accessible an InvalidArgumentException exception will be thrown. The class uses PHP's `finfo` class to detect the required mediatype as defined in RFC2045.

## Path representations

### String representation

Basic path representations is done using the following methods:

~~~php
use League\Uri\Components\DataPath as Path;

$path = new Path('text/plain;charset=us-ascii,Hello%20World%21');
$path->__toString(); //returns 'text/plain;charset=us-ascii,Hello%20World%21'
$path->getUriComponent(); //returns 'text/plain;charset=us-ascii,Hello%20World%21'
~~~

## Properties

### Attributes

The DataPath class exposes the following specific methods:

- `getMediaType`: This method returns the Data URI current mediatype;
- `getMimeType`: This method returns the Data URI current mimetype;
- `getParameters`: This method returns the parameters associated with the mediatype;
- `getData`: This methods returns the encoded data contained is the Data URI;

Each of these methods return a string. This string can be empty if the data where no supplied when constructing the URI.

~~~php
use League\Uri\Components\DataPath as Path;

$uri = DataUri::createFromString('data:text/plain;charset=us-ascii,Hello%20World%21');
echo $uri->getMediaType(); //returns 'text/plain;charset=us-ascii'
echo $uri->getMimeType(); //returns 'text/plain'
echo $uri->getParameters(); //returns 'charset=us-ascii'
echo $uri->getData(); //returns 'Hello%20World%21'
~~~

### Is it a binary data ?

To tell whether the data URI represents some binary data you can call the `isBinaryData` method. This method which returns a boolean will return `true` if the data is in a binary state. The binary state is checked on instantiation. Invalid binary dataURI will throw an `InvalidArgumentException` exception on initiation.

~~~php
use League\Uri\Components\DataPath as Path;

$uri = DataUri::createFromPath('path/to/my/png/image.png');
$uri->isBinaryData(); //returns true
$altUri = DataUri::createFromString('data:text/plain;charset=us-ascii,Hello%20World%21');
$altUri->isBinaryData(); //returns false
~~~

## Manipulation

The data URI Path class is an immutable object everytime you manipulate the object a new object is returned with the modified value if needed.

### Update the Data URI parameters

Since we are dealing with a data and not just a URI, the only property that can be easily modified are its optional parameters.

To set new parameters you should use the `withParameters` method:

~~~php
use League\Uri\Components\DataPath as Path;

$path = new Path('text/plain;charset=us-ascii,Hello%20World%21');
$newPath = $path->withParameters('charset=utf-8');
echo $newPath; //returns 'text/plain;charset=utf-8,Hello%20World%21'
~~~

<p class="message-notice">Of note the data should be urlencoded if needed.</p>

### Transcode the data between its binary and ascii representation

Another manipulation is to transcode the data from ASCII to is base64 encoded (or binary) version. If no conversion is possible the former object is returned otherwise a new valid data uri object is created.

~~~php
use League\Uri\Components\DataPath as Path;

$path = new Path('data:text/plain;charset=us-ascii,Hello%20World%21');
$path->isBinaryData(); // return false;
$newPath = $path->toBinary();
$newPath->isBinaryData(); //return true;
$newPath->toAscii()->sameValueAs($path); //return true;
~~~

## Saving the Data URI Path

Since the path can be interpreted as a file, it is possible to save it to a specified path using the dedicated `save` method. This method accepts two parameters:

- the file path;
- the open mode (à la PHP `fopen`);

By default the open mode is set to `w`. If for any reason the file is not accessible a `RuntimeException` will be thrown.

The method returns the `SplFileObject` object used to save the data-uri data for further analysis/manipulation if you want.

~~~php
use League\Uri\Components\DataPath as Path;

$uri = Path::createFromPath('path/to/my/file.png');
$file = $uri->save('path/where/to/save/my/image.png');
//$file is a SplFileObject which point to the newly created file;
~~~
