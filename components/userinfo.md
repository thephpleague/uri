---
layout: default
title: The User Information part
---

# The User Information part

The library provides a `League\Uri\Components\UserInfo` class to ease interacting with the user information URI part.

## Instantiation

### Using the default constructor

The constructor expects 2 optional arguments:

- the user login: a `League\Uri\Components\User` object, a string or the `null` value;
- the user password: a `League\Uri\Components\Pass` object, a string or the `null` value;

~~~php
use League\Uri\Components;

$info = new Components\UserInfo('foo', 'bar');
echo $info; //display 'foo:bar'

$emptyInfo = new Components\UserInfo();
echo $emptyInfo; //display ''

$altInfo = new Components\UserInfo(
	new Components\User('foo'),
	new Components\Pass('bar')
);
echo $altInfo; //display 'foo:bar'
~~~

<p class="message-warning">If the submitted value are not valid user and/or password string an <code>InvalidArgumentException</code> will be thrown.</p>

### Using a Uri object

You can also get a `UserInfo` object from a Hierarchical URI object:

~~~php
use League\Uri\Schemes\Ws as WsUri;

$uri = WsUri::createFromComponents(parse_url('http://john:doe@example.com:81/'));
$userInfo = $uri->userInfo; //return a League\Uri\Components\UserInfo object
echo $userInfo; // display 'john:doe'
~~~

## User info representations

### String representation

Basic representations is done using the following methods:

~~~php
use League\Uri\Components\UserInfo;

$info = new UserInfo('foo', 'bar');
$info->__toString();      //return 'foo:bar'
$info->getUriComponent(); //return 'foo:bar@'
~~~

## Accessing User information content

To acces the user login and password information you need to call the respective `UserInfo::getUser` and `UserInfo::getPass` methods like shown below.

~~~php
use League\Uri\Components\UserInfo;
use League\Uri\Schemes\Http;

$info = new UserInfo('foo', 'bar');
$info->getUser(); //return 'foo'
$info->getPass(); //return 'bar'

$uri = Http::createFromString('http://john:doe@example.com:81/');
$uri->userInfo->getUser(); //return 'john'
$uri->userInfo->getPass(); //return 'doe'
~~~

To get access to the component classes you can use PHP's magic `__get` method:

~~~php
use League\Uri\Components\UserInfo;
use League\Uri\Schemes\Http;

$info = new UserInfo('foo', 'bar');
$info->user; //return a League\Uri\Components\User class
$info->pass; //return a League\Uri\Components\Pass class

$uri = Http::createFromString('http://john:doe@example.com:81/');
$uri->userInfo->user->__toString(); //return 'john'
$uri->userInfo->pass->__toString(); //return 'doe'
~~~

## Modifying the user information

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

Because the `UserInfo` class does not represent a URI component, it does not include a `modify` method.
To modify the user login and password information you need to call the respective `UserInfo::withUser` and `UserInfo::withPass` methods like shown below.

~~~php
use League\Uri\Components\UserInfo;

$info = new UserInfo('foo', 'bar');
$new_info = $info->withUser('john')->withPass('doe');
echo $new_info; //displays john:doe
echo $info;     //displays foo:bar
~~~

<p class="message-warning">When a modification fails a <code>InvalidArgumentException</code> is thrown.</p>
