---
layout: default
title: The User information component
---

The UserInfo
=======

The library provides a `UserInfo` class to ease user information creation and manipulation. This URI component object exposes the [package common API](/5.0/components/api/), but also provide specific methods to work with the URI user information part.

## Accessing User information content

~~~php
<?php

public UserInfo::getUser(int $enc_type = ComponentInterface::RFC3986_ENCODING): string|null
public UserInfo::getPass(int $enc_type = ComponentInterface::RFC3986_ENCODING): string|null
~~~

To access the user login and password information you need to call the respective `UserInfo::getUser` and `UserInfo::getPass` methods like shown below.

~~~php
<?php

use League\Uri\Components\UserInfo;

$info = new UserInfo('foo', 'bar');
$info->getUser(); //return 'foo'
$info->getPass(); //return 'bar'
~~~

Just like the `ComponentInterface::getContent` method both `UserInfo::getUser` and `UserInfo::getPass` accept an optional `$enc_type` argument to specify how to encode the specify how to encode the returned value.

~~~php
<?php

use League\Uri\Components\UserInfo;

$info = new UserInfo('bébé');
$info->getUser(UserInfo::RFC3987_ENCODING); //return 'bébé'
$info->getUser(UserInfo::RFC3986_ENCODING); //return 'b%C3%A9b%C3%A9'
$info->getUser();                           //return 'b%C3%A9b%C3%A9'
~~~

## Modifying the user information

~~~php
<?php

public UserInfo::withUserInfo(string $user [, string $password = null]): self
~~~

<p class="message-notice">If the modifications do not change the current object, it is returned as is, otherwise, a new modified object is returned.</p>

Because the `UserInfo` is composed of at most two components the `UserInfo::withUserInfo` method is introduced to ease modify the object content.

~~~php
<?php

use League\Uri\Components\UserInfo;

$info = new UserInfo('foo', 'bar');
$new_info = $info->withUserInfo('john', 'doe');
echo $new_info; //displays john:doe
echo $info;     //displays foo:bar
~~~

<p class="message-warning">If the modification is invalid a <code>InvalidArgumentException</code> exception is thrown.</p>
