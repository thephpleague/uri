---
layout: default
title: URIs extension
redirect_from:
    - /4.0/uri/extension/
---

# Creating other URI objects

## Creating an URI object similar to HTTP URI

Let say you want to create a `telnet` class to handle telnet URI. You just need to extends the <code>League\Uri\Schemes\Generic\AbstractHierarchicalUri</code> object and add telnet specific validation features to your class. Here's a quick example that you can further improve.

~~~php
<?php

namespace Example;

use League\Uri\Schemes\Generic\AbstractHierarchicalUri;
use League\Uri\Interfaces\Uri;

class Telnet extends AbstractHierarchicalUri implements Uri
{
    /**
     * Supported Schemes with their associated port
     *
     * This property override the Parent supportedSchemes empty array
     *
     * @var array
     */
    protected static $supportedSchemes = [
        'telnet' => 23,
    ];

    /**
     * Validate any changes made to the URI object
     *
     * This method override the Parent isValid method
     * When it returns false a RuntimeException is thrown
     *
     * @return bool
     */
    protected function isValid()
    {
        return empty($this->fragment->__toString())
            && $this->isValidGenericUri()
            && $this->isValidHierarchicalUri();
    }
}
~~~

And now you can easily make it works against any `telnet` scheme URI

~~~php
<?php

use Example\Telnet;

$uri = Telnet::createFromString('TeLnEt://example.com:23/Hello%20There'):
echo $uri; //return telnet://example.com/Hello%20There
Telnet::createFromString('http://example.org'): //will throw an InvalidArgumentException
~~~

Of course you are free to add more methods to fulfill your own requirements. But remember that all general URI [properties](/uri/4.0/uri/properties/) and [methods](/uri/4.0/uri/manipulation/#basic-modifications) and [modifiers](/uri/4.0/uri/manipulation/#uri-modifiers) are already usable with these simple steps.

## Creating a Generic URI Object

Since each URI specific schemes follow its own validation rules they need their own class. The library can help you speed up your process to create such class. As an example we will implement the `mailto` scheme.

`mailto` URIs are specific in the fact that :

- they do not have any authority part and fragment components;
- their paths are made of urlencoded emails separated by a comma;
- they accept any email header as query string parameters;

These general rules are taken from reading [mailto URI RFC](http://tools.ietf.org/html/rfc6068).

Here's how we will proceed. We will:

- create the needed interfaces;
- implement the concrete classes;

<p class="message-info">Using interfaces will garantee interoperability between the classes we are creating and the other package components.</p>

### The MailtoPathInterface interface

The main specific area of the `mailto` scheme URI is the path. It only contains valid emails separated by a comma as per RFC specification. It means we need an interface to manipulate the path as a collection of emails. So we can remove/append/prepend/replace/filter emails as we want. As a matter a fact there's already a interface for that in the library. To complete this interface we just need one method to retrieve one specific email from the path based on its index.

~~~php
<?php

namespace Example;

use League\Uri\Interfaces\HierarchicalComponent;
use League\Uri\Interfaces\Path;

interface MailtoPathInterface extends Path, HierarchicalComponent
{
    /**
     * Retrieves a single host label.
     *
     * Retrieves a single host label. If the label offset has not been set,
     * returns the default value provided.
     *
     * @param string $offset  the label offset
     * @param mixed  $default Default value to return if the offset does not exist.
     *
     * @return mixed
     */
    public function getEmail($offset, $default = null);
}
~~~

<p class="message-notice">The <code>MailtoPathInterface</code> extends the package <code>Path</code> interface to inherit basic operations done on any URI path component.</p>

### The MailtoPath and the Mailto classes

Now that we have defined a new specialized path interface. Let's implement it in the `MailtoPath` class. The library abstract classes and traits will help a lot.

- the `AbstractHierarchicalComponent` abstract class will add all manipulating methods needed. As well as all collections related methods to the class.
- the `PathTrait` trait will add basic path operations independent

We simply need to add:

- the path validating methods
- the method to retrieve one email.

~~~php
<?php

namespace Example;

use League\Uri\Components\AbstractHierarchicalComponent;
use InvalidArgumentException;

class MailtoPath extends AbstractHierarchicalComponent implements MailtoPathInterface
{
    use PathTrait;

    /**
     * The path separator as described in RFC6068
     *
     * Must be static to work with the named constructors methods
     */
    protected static $separator = ',';

    /**
     * New instance
     *
     * @param string $emails
     */
    public function __construct($path = '')
    {
        if (!empty($path)) {
            $this->data = $this->validate($path);
        }
    }

    /**
     * validate the submitted data
     *
     * @param string $path
     *
     * @return array
     */
    protected function validate($path)
    {
        $emails = array_map('rawurldecode', explode(static::$separator, $path));
        $emails = array_map('trim', $emails);
        $emails = array_filter($emails);
        if (empty($emails)) {
            return [];
        }
        $verif = filter_var($emails, FILTER_VALIDATE_EMAIL, FILTER_REQUIRE_ARRAY);
        if ($emails !== $verif) {
            throw new InvalidArgumentException('the submitted path is invalid');
        }
        return $emails;
    }

    /**
     * format the string before manipulation methods
     *
     * @param string[] $str
     * @param int      $type
     */
    protected static function formatComponentString($data, $type)
    {
        return implode(static::$separator, static::validateIterator($data));
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail($key, $default = null)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * return the string representation of the path
     */
    public function __toString()
    {
        return implode(static::$separator, array_map('rawurlencode', $this->data));
    }
}
~~~

Now that we have a `MailtoPath` class we can create the main `Mailto` class.

This time we will built the URI object by extending the <code>League\Uri\Schemes\Generic\AbstractUri</code> object by adding `mailto` specific validation rules to the class.

~~~php
<?php

namespace Example;

use League\Uri\Components\Fragment;
use League\Uri\Components\Host;
use League\Uri\Components\Port;
use League\Uri\Components\Query;
use League\Uri\Components\Scheme;
use League\Uri\Components\UserInfo;
use League\Uri\Interfaces\Fragment as FragmentInterface;
use League\Uri\Interfaces\Host as HostInterface;
use League\Uri\Interfaces\Port as PortInterface;
use League\Uri\Interfaces\Query as QueryInterface;
use League\Uri\Interfaces\Scheme as SchemeInterface;
use League\Uri\Interfaces\UserInfo as UserInfoInterface;
use League\Uri\Interfaces\Uri;
use League\Uri\Schemes\Generic\AbstractUri;
use League\Uri\UriParser;

class Mailto extends AbstractUri implements Uri
{
    /**
     * Create a new instance of URI
     *
     * @param SchemeInterface     $scheme
     * @param UserInfoInterface   $userInfo
     * @param HostInterface       $host
     * @param PortInterface       $port
     * @param MailtoPathInterface $path
     * @param QueryInterface      $query
     * @param FragmentInterface   $fragment
     */
    public function __construct(
        SchemeInterface $scheme,
        UserInfoInterface $userInfo,
        HostInterface $host,
        PortInterface $port,
        MailtoPathInterface $path,
        QueryInterface $query,
        FragmentInterface $fragment
    ) {
        $this->scheme = $scheme;
        $this->userInfo = $userInfo;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
        $this->assertValidObject();
    }

    /**
     * Validate any changes made to the URI object
     *
     * This method override the Parent isValid method
     * When it returns false an InvalidArgumentException is thrown
     *
     * @return bool
     */
    protected function isValid()
    {
        if ('mailto:' !== $this->scheme->getUriComponent()) {
            throw new InvalidArgumentException(
                'The submitted scheme is invalid for the class '.get_class($this)
            );
        }

        $expected = 'mailto:'
            .$this->path->getUriComponent()
            .$this->query->getUriComponent();

        return $this->isValidGenericUri()
            && $this->__toString() === $expected;
    }

    /**
     * Create a new instance from a string
     *
     * @param string $uri
     *
     * @return static
     */
    public static function createFromString($uri = '')
    {
        return static::createFromComponents((new UriParser())->parse($uri));
    }

    /**
     * Create a new instance from a hash of parse_url parts
     *
     * This method override the Parent constructor method
     * And make sure the path is constructed with a MailtoPath instance
     *
     * @param array $components
     *
     * @return static
     */
    public static function createFromComponents(array $components)
    {
        $components = self::normalizeUriHash($components);

        return new static(
            new Scheme($components['scheme']),
            new UserInfo($components['user'], $components['pass']),
            new Host($components['host']),
            new Port($components['port']),
            new MailtoPath($components['path']),
            new Query($components['query']),
            new Fragment($components['fragment'])
        );
    }

    /**
     * A specific named constructor to speed up
     * creating a new instance from a collection of mails
     *
     * @param \Traversable|string[] $emails
     *
     * @return static
     */
    public static function createFromEmails($emails)
    {
        return new static(
            new Scheme('mailto'),
            new UserInfo(),
            new Host(),
            new Port(),
            MailtoPath::createFromArray($emails),
            new Query(),
            new Fragment()
        );
    }
}
~~~

Et voilà! You can already do this:

~~~php
<?php

use Example\Mailto;
use League\Uri\Modifiers\MergeQuery;

$mailto = Mailto::createFromEmails(['foo@example.com', 'info@thephpleague.com']);
$mailto->__toString();
//returns 'mailto:foo@xexample.com,info@thephpleague.com';

echo $mailto->path->getEmail(0); //returns 'foo@example.com'

var_dump($mailto->path->toArray());
//returns ['foo@example.com', 'info@thephpleague.com']

$subject = http_build_query(['subject' => 'Hello World!'], '', '&', PHP_QUERY_RFC3986);
$newMailto = (new MergeQuery($subject))->__invoke($mailto);
$newMailto->__toString();
//returns 'mailto:foo@example.com,info@thephpleague.com?subject=Hello%20World%21';
~~~

<p class="message-notice">There are still room for improvement by adding specific URI modifiers but I'll leave that to you to strengthen the above code.</p>