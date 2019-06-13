---
layout: default
title: The User information component
---

The UserInfo
=======

The `League\Uri\Components\UserInfo` class eases user information creation and manipulation.
This URI component object exposes the [package common API](/components/2.0/api/),
but also provide specific methods to work with the URI user information part.

## Creating a new object

~~~php
public UserInfo::__construct($user, $pass = null): void
~~~

<p class="message-notice">submitted string is normalized to be <code>RFC3986</code> compliant.</p>

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Exceptions\SyntaxError</code> exception is thrown.</p>

## Accessing User information content

~~~php
public UserInfo::getUser(): ?string
public UserInfo::getPass(): ?string
~~~

To access the user login and password information you need to call the respective `UserInfo::getUser` and `UserInfo::getPass` methods like shown below.

~~~php
$info = new UserInfo('foo', 'bar');
$info->getUser(); //return 'foo'
$info->getPass(); //return 'bar'
~~~

## Modifying the user information

~~~php
public UserInfo::withUserInfo($user, $password = null): self
~~~

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

Because the `UserInfo` is composed of at most two components the `UserInfo::withUserInfo` method is introduced to ease modify the object content.

~~~php
$info = new UserInfo('foo', 'bar');
$new_info = $info->withUserInfo('john', 'doe');
echo $new_info; //displays john:doe
echo $info;     //displays foo:bar
~~~

<p class="message-warning">If the submitted value is not valid a <code>League\Uri\Exceptions\SyntaxError</code> exception is thrown.</p>
