---
layout: default
title: The User information component
---

The UserInfo
=======

The library provides a `UserInfo` class to ease complex user information manipulation. The object implements the `League\Uri\Interfaces\Component` Interface.

## Accessing User information content

~~~php
<?php

public UserInfo::getUser(string $enc_type = Component::RFC3986_ENCODING): string|null
public UserInfo::getPass(string $enc_type = Component::RFC3986_ENCODING): string|null
~~~

To acces the user login and password information you need to call the respective `UserInfo::getUser` and `UserInfo::getPass` methods like shown below.

~~~php
<?php

use League\Uri\Components\UserInfo;
use League\Uri\Schemes\Http;

$info = new UserInfo('foo', 'bar');
$info->getUser(); //return 'foo'
$info->getPass(); //return 'bar'

$uri = Http::createFromString('http://john:doe@example.com:81/');
$uri->userInfo->getUser(); //return 'john'
$uri->userInfo->getPass(); //return 'doe'
~~~

Just like the `Component::getContent` method both `UserInfo::getUser` and `UserInfo::getPass` accept an optional `$enc_type` argument to specify how to encode the specify how to encode the returned value.

## Modifying the user information

~~~php
<?php

public UserInfo::withUserInfo(string $user [, string $password = null]): self
~~~

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

Because the `UserInfo` is composed of at most two component the `UserInfo::withUserInfo` method is introduced to ease modify the object content.

~~~php
<?php

use League\Uri\Components\UserInfo;

$info = new UserInfo('foo', 'bar');
$new_info = $info->withUserInfo('john', 'doe');
echo $new_info; //displays john:doe
echo $info;     //displays foo:bar
~~~

<p class="message-warning">If the modification is invalid a <code>InvalidArgumentException</code> exception is thrown.</p>
