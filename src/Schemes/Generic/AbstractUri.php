<?php
/**
 * League.Uri (http://uri.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.1.1
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Schemes\Generic;

use InvalidArgumentException;
use League\Uri\Interfaces\Fragment;
use League\Uri\Interfaces\Host;
use League\Uri\Interfaces\Path;
use League\Uri\Interfaces\Port;
use League\Uri\Interfaces\Query;
use League\Uri\Interfaces\Scheme;
use League\Uri\Interfaces\UserInfo;
use League\Uri\Types\ImmutablePropertyTrait;
use RuntimeException;

/**
 * common URI Object properties and methods
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 */
abstract class AbstractUri
{
    use AuthorityValidatorTrait;

    use ImmutablePropertyTrait;

    use UriBuilderTrait;

    /**
     * Host Component
     *
     * @var Host
     */
    protected $host;

    /**
     * Scheme Component
     *
     * @var Scheme
     */
    protected $scheme;

    /**
     * User Information Part
     *
     * @var UserInfo
     */
    protected $userInfo;

    /**
     * Port Component
     *
     * @var Port
     */
    protected $port;

    /**
     * Path Component
     *
     * @var Path
     */
    protected $path;

    /**
     * Query Component
     *
     * @var Query
     */
    protected $query;

    /**
     * Fragment Component
     *
     * @var Fragment
     */
    protected $fragment;

    /**
     * Supported Schemes
     *
     * @var array
     */
    protected static $supportedSchemes = [];

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     *
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return $this->scheme->__toString();
    }

    /**
     * Return an instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     *
     * @throws InvalidArgumentException for invalid schemes.
     * @throws InvalidArgumentException for unsupported schemes.
     * @throws RuntimeException         if the returned URI object is invalid.
     *
     * @return static A new instance with the specified scheme.
     */
    public function withScheme($scheme)
    {
        return $this->withProperty('scheme', $this->filterPropertyValue($scheme));
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        return $this->userInfo->__toString();
    }

    /**
     * Return an instance with the specified user information.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param string      $user     The user name to use for authority.
     * @param null|string $password The password associated with $user.
     *
     * @throws RuntimeException if the returned URI object is invalid.
     *
     * @return static A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        if (null === $password) {
            $password = '';
        }

        $userInfo = $this->userInfo->withUser($this->filterPropertyValue($user))->withPass($password);
        if ($this->userInfo->getUser() == $userInfo->getUser()
            && $this->userInfo->getPass() == $userInfo->getPass()
        ) {
            return $this;
        }

        $clone = clone $this;
        $clone->userInfo = $userInfo;

        return $clone;
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     *
     * @return string The URI host.
     */
    public function getHost()
    {
        return $this->host->__toString();
    }

    /**
     * Return an instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host The hostname to use with the new instance.
     *
     * @throws InvalidArgumentException for invalid hostnames.
     * @throws RuntimeException         if the returned URI object is invalid.
     *
     * @return static A new instance with the specified host.
     */
    public function withHost($host)
    {
        return $this->withProperty('host', $this->filterPropertyValue($host));
    }

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The URI port.
     */
    public function getPort()
    {
        return $this->hasStandardPort() ? null : $this->port->toInt();
    }

    /**
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port The port to use with the new instance; a null value
     *                       removes the port information.
     *
     * @throws InvalidArgumentException for invalid ports.
     * @throws RuntimeException         if the returned URI object is invalid.
     *
     * @return static A new instance with the specified port.
     */
    public function withPort($port)
    {
        return $this->withProperty('port', $port);
    }

    /**
     * Retrieve the path component of the URI.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     *
     * @return string The URI path.
     */
    public function getPath()
    {
        return $this->path->__toString();
    }

    /**
     * Return an instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified path.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * If the path is intended to be domain-relative rather than path relative then
     * it must begin with a slash ("/"). Paths not starting with a slash ("/")
     * are assumed to be relative to some base path known to the application or
     * consumer.
     *
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param string $path The path to use with the new instance.
     *
     * @throws InvalidArgumentException for invalid paths.
     * @throws RuntimeException         if the returned URI object is invalid.
     *
     * @return static A new instance with the specified path.
     */
    public function withPath($path)
    {
        return $this->withProperty('path', $path);
    }

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method MUST return an empty string.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     *
     * @return string The URI query string.
     */
    public function getQuery()
    {
        return $this->query->__toString();
    }

    /**
     * Return an instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query The query string to use with the new instance.
     *
     * @throws InvalidArgumentException for invalid query strings.
     * @throws RuntimeException         if the returned URI object is invalid.
     *
     * @return static A new instance with the specified query string.
     */
    public function withQuery($query)
    {
        return $this->withProperty('query', $this->filterPropertyValue($query));
    }

    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method MUST return an empty string.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     *
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        return $this->fragment->__toString();
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment The fragment to use with the new instance.
     *
     * @throws RuntimeException if the returned URI object is invalid.
     *
     * @return static A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {
        return $this->withProperty('fragment', $this->filterPropertyValue($fragment));
    }

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     *
     * @return string
     */
    public function __toString()
    {
        return $this->scheme->getUriComponent().$this->getSchemeSpecificPart();
    }

    /**
     * Retrieve the scheme specific part of the URI.
     *
     * If no specific part information is present, this method MUST return an empty
     * string.
     *
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    protected function getSchemeSpecificPart()
    {
        $auth = $this->getAuthority();
        if ('' !== $auth) {
            $auth = '//'.$auth;
        }

        return $auth
            .$this->formatPath($this->path->getUriComponent(), $auth)
            .$this->query->getUriComponent()
            .$this->fragment->getUriComponent();
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     *
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        $port = '';
        if (!$this->hasStandardPort()) {
            $port = $this->port->getUriComponent();
        }

        return $this->userInfo->getUriComponent().$this->host->getUriComponent().$port;
    }

    /**
     * Returns whether the standard port for the given scheme is used, when
     * the scheme is unknown or unsupported will the method return false
     *
     * @return bool
     */
    protected function hasStandardPort()
    {
        $port = $this->port->toInt();
        if (null === $port) {
            return true;
        }

        $scheme = $this->scheme->__toString();
        if ('' === $scheme) {
            return false;
        }

        return isset(static::$supportedSchemes[$scheme])
            && static::$supportedSchemes[$scheme] === $port;
    }

    /**
     * Assert if the current URI object is valid
     *
     * @throws RuntimeException if the resulting URI is not valid
     */
    protected function assertValidObject()
    {
        if (!$this->isValid()) {
            throw new RuntimeException('The URI properties will produce an invalid `'.get_class($this).'`');
        }
    }

    /**
     * Tell whether the current URI is valid.
     *
     * The URI object validity depends on the scheme. This method
     * MUST be implemented on every URI object
     *
     * @return bool
     */
    abstract protected function isValid();

    /**
     *  Tell whether any generic URI is valid
     *
     * @return bool
     */
    protected function isValidGenericUri()
    {
        $path = $this->path->getUriComponent();
        if (false === strpos($path, ':')) {
            return true;
        }

        $path = explode(':', $path);
        $path = array_shift($path);
        $str = $this->scheme->getUriComponent().$this->getAuthority();

        return !('' === $str && strpos($path, '/') === false);
    }

    /**
     * Tell whether Http URI like scheme URI are valid
     *
     * @return bool
     */
    protected function isValidHierarchicalUri()
    {
        $this->assertSupportedScheme();

        return $this->isAuthorityValid();
    }

    /**
     * Assert whether the current scheme is supported by the URI object
     *
     * @throws InvalidArgumentException If the Scheme is not supported
     */
    protected function assertSupportedScheme()
    {
        $scheme = $this->getScheme();
        if (!isset(static::$supportedSchemes[$scheme])) {
            throw new InvalidArgumentException('The submitted scheme is unsupported by `'.get_class($this).'`');
        }
    }
}
