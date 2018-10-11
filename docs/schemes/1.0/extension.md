---
layout: default
title: URIs extension
redirect_from:
    - /5.0/uri/extension/
---

# Creating other URI objects

<p class="message-warning">Starting with version <code>1.1.0</code> all URI objects are defined in the <code>League\Uri</code> namespace. The <code>League\Uri\Schemes</code> namespace is deprecated and will be removed in the next major release.</p>

## Creating a simple URI object

Let say you want to create a `telnet` class to handle telnet URI. You just need to extends the <code>League\Uri\AbstractUri</code> object and add telnet specific validation features to your class. Here's a quick example that you can further improve.

~~~php
<?php

namespace Example;

use League\Uri\AbstractUri;

class Telnet extends AbstractUri
{
    /**
     * Supported Schemes with their associated port
     *
     * This property override the Parent supported_schemes empty array
     *
     * @var array
     */
    protected static $supported_schemes = [
        'telnet' => 23,
    ];

    /**
     * Validate any changes made to the URI object
     *
     * This method override the Parent isValidUri method
     * When it returns false a InvalidArgumentException is thrown
     *
     * @return bool
     */
    protected function isValidUri()
    {
        return null === $this->fragment
            && '' !== $this->host
            && (null === $this->scheme || isset(static::$supported_schemes[$this->scheme]))
            && !('' != $this->scheme && null === $this->host);
    }
}
~~~

And now you can easily make it works against any `telnet` scheme URI

~~~php
<?php

use Example\Telnet;

$uri = Telnet::createFromString('TeLnEt://example.com:23/Hello%20There'):
echo $uri; //return telnet://example.com/Hello%20There
Telnet::createFromString('http://example.org'):
//will throw an League\Uri\UriException
~~~

## Advance URI Object creation

Since each URI specific schemes follow its own validation rules they need their own class. The library can help you speed up your process to create such class. As an example we will implement the `mailto` scheme.

`mailto` URIs are specific in the fact that :

- they do not have any authority part and fragment components;
- their paths are made of urlencoded emails separated by a comma;

We simply need to add:

- the path validating methods
- enforce the URI specific validation state

~~~php
<?php

namespace Example;

use League\Uri\AbstractUri;
use League\Uri\UriException;

class Mailto extends AbstractUri
{
    /**
     * Validate any changes made to the URI object
     *
     * This method override the Parent isValidUri method
     * When it returns false an InvalidArgumentException is thrown
     *
     * @return bool
     */
    protected function isValidUri()
    {
        return 'mailto' === $this->scheme
            && null === $this->fragment
            && null === $this->authority
    }

    /**
     * Filter the Path component
     *
     * This method override the Parent filterPath method
     *
     * @param string $path
     *
     * @throws UriException If the path is not compliant
     *
     * @return string
     */
    protected function filterPath($path)
    {
        if ('' == $path) {
            throw new UriException('the submitted path can not be empty');
        }

        $emails = array_map('rawurldecode', explode(',', $path));
        $emails = array_map('trim', $emails);
        $emails = array_filter($emails);
        if (empty($emails)) {
            throw new UriException('the submitted path contains empty emails');
        }

        $verif = filter_var($emails, FILTER_VALIDATE_EMAIL, FILTER_REQUIRE_ARRAY);
        if ($emails !== $verif) {
            throw new UriException('the submitted path contains invalid emails');
        }

        return $path;
    }

    /**
     * A specific named constructor to speed up
     * creating a new instance from a collection of mails
     *
     * @param string[] $emails
     *
     * @return static
     */
    public static function createFromEmails(array $emails)
    {
        $verif = filter_var($emails, FILTER_VALIDATE_EMAIL, FILTER_REQUIRE_ARRAY);
        if ($emails !== $verif) {
            throw new Exception('the submitted emails are invalid');
        }

        $path = implode(',', array_map('rawurlencode', $emails));

        return new static::createFromComponents(['scheme' => 'mailto', 'path' => $path]);
    }
}
~~~

Et voilà! You can already do this:

~~~php
<?php

use Example\Mailto;

$subject = http_build_query(['subject' => 'Hello World!'], '', '&', PHP_QUERY_RFC3986);
$mailto = Mailto::createFromEmails(['foo@example.com', 'info@thephpleague.com'])
    ->withQuery($subject);
echo $mailto;
//displays 'mailto:foo@example.com,info@thephpleague.com?subject=Hello%20World%21';
~~~

## URI manipulations

Of course you are free to add more methods to fulfill your own requirements. But remember that the URI common API and <a href="/5.0/manipulations/middlewares/">the URI middlewares</a> are already usable with these simple steps.
