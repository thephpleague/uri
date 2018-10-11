---
layout: default
title: The Fragment component
redirect_from:
    - /4.0/components/fragment/
---

# The Fragment component

The library provides a `Fragment` class to ease fragment creation and manipulation.

## Instantiation

### Using the default constructor.

~~~php
<?php

public function __contruct($fragment = null)
~~~

The constructor accepts:

- a valid string according to their component validation rules as explain in RFC3986;
- the `null` value;

~~~php
<?php

use League\Uri\Components\Fragment;

$fragment = new Fragment('eur%20o');
echo $fragment->getContent();      //display 'eur%20o'
echo $fragment;                    //display 'eur%20o'
echo $fragment->getUriComponent(); //display '#eur%20o'

$fragment = new Fragment();
echo $fragment->getContent();      //display null
echo $fragment;                    //display ''
echo $fragment->getUriComponent(); //display ''

$fragment = new Fragment('');
echo $fragment->getContent();      //display ''
echo $fragment;                    //display ''
echo $fragment->getUriComponent(); //display '#'
~~~

<p class="message-warning">If the submitted value is not a valid, an <code>InvalidArgumentException</code> exception is thrown.</p>

<p class="message-info">On instantiation the submitted string is normalized using RFC3986 rules.</p>

### Using a League Uri object

You can acces a `League\Uri\Components\Fragment` object with an already instantiated League Uri object.

~~~php
<?php

use League\Uri\Fragments\Http as HttpUri;

$uri  = HttpUri::createFromString('http://uri.thephpleague.com:82');
$fragment = $uri->fragment; // $fragment is a League\Uri\Components\Fragment object;
~~~

## Properties

The component representation, comparison and manipulation is done using the package [UriPart](/uri/4.0/components/overview/#uri-part-interface) and the [Component](/uri/4.0/components/overview/#uri-component-interface) interfaces.

### Fragment::getDecoded

<p class="message-notice">New since <code>version 4.2</code></p>

Returns the decoded value of a fragment component

~~~php
<?php

public Fragment::getDecoded(void): null|string
~~~

#### Example

~~~php
<?php

use League\Uri\Components\Fragment;

$component = new Fragment('%E2%82%AC');
echo $component->getUriComponent(); //displays '#%E2%82%AC'
echo $component->getDecoded(); //displays '€'
~~~