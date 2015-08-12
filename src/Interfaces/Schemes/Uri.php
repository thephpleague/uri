<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @package   League.uri
 * @author    Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @copyright 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/uri/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @link      https://github.com/thephpleague/uri/
 */
namespace League\Uri\Interfaces\Schemes;

use League\Uri\Interfaces\Components\Collection;
use Psr\Http\Message\UriInterface;

/**
 * Value object representing a URI.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package League.uri
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.0.0
 * @see     https://tools.ietf.org/html/rfc3986
 */
interface Uri
{
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
    public function getScheme();

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
     * @throws \InvalidArgumentException for invalid schemes.
     * @throws \InvalidArgumentException for unsupported schemes.
     *
     * @return self A new instance with the specified scheme.
     *
     */
    public function withScheme($scheme);
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
    public function getAuthority();

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
    public function getUserInfo();

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
    public function getHost();

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
    public function getPort();

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
    public function getPath();

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
    public function getQuery();

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
    public function getFragment();

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
     * @return self A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null);

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
     * @throws \InvalidArgumentException for invalid hostnames.
     * @return self                      A new instance with the specified host.
     *
     */
    public function withHost($host);

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
     * @throws \InvalidArgumentException for invalid ports.
     * @return self                      A new instance with the specified port.
     *
     */
    public function withPort($port);

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
     * @throws \InvalidArgumentException for invalid paths.
     * @return self                      A new instance with the specified path.
     *
     */
    public function withPath($path);

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
     * @throws \InvalidArgumentException for invalid query strings.
     * @return self                      A new instance with the specified query string.
     *
     */
    public function withQuery($query);

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
     * @return self A new instance with the specified fragment.
     */
    public function withFragment($fragment);

    /**
     * Return an instance without dot segments according to RFC3986 algorithm
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without dot segment according to RFC3986 algorithm
     *
     * @return static
     */
    public function withoutDotSegments();

    /**
     * Return an instance with update query values
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query data
     *
     * @param mixed $query the data to be merged query can be
     *                     - another Interfaces\Query object
     *                     - a Traversable object
     *                     - an array
     *                     - a string or a Stringable object
     *
     * @return static
     */
    public function mergeQuery($query);

    /**
     * Return an instance with a query string sorted by offset, maintaining offset to data correlations.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified query
     *
     * @param callable|int $sort a PHP sort flag constant or a comparaison function
     *                           which must return an integer less than, equal to,
     *                           or greater than zero if the first argument is
     *                           considered to be respectively less than, equal to,
     *                           or greater than the second.
     *
     * @return static
     */
    public function ksortQuery($sort = SORT_REGULAR);

    /**
     * Return an instance without the specified query values
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without the specified query data
     *
     * @param callable|array $keys the list of keys to remove from the query
     *                             if a callable is given it should filter the list
     *                             of keys to remove from the query string
     *
     * @return static
     */
    public function withoutQueryValues($keys);

    /**
     * Return an instance with the filtered query values
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the filtered query data
     *
     * @param callable $callable the callable should filter the list
     *                           of keys to remain in the query
     *
     * @param int $flag Flag determining what argument are sent to callback
     *
     * @return static
     */
    public function filterQuery(callable $callable, $flag = Collection::FILTER_USE_VALUE);

    /**
     * Return an instance with the host in his IDN form
     *
     * This method MUST retain the state of the current instance, and return
     * an instance with the host in its IDN form using RFC 3492 rules
     *
     * @see http://tools.ietf.org/html/rfc3492
     *
     * @return static
     */
    public function hostToUnicode();

    /**
     * Return an instance with the host in his punycode encoded form
     *
     * This method MUST retain the state of the current instance, and return
     * an instance with the host transcoded using to ascii the RFC 3492 rules
     *
     * @see http://tools.ietf.org/html/rfc3492
     *
     * @return static
     */
    public function hostToAscii();

    /**
     * Return an instance without the host zone identifier according to RFC6874
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without the host zone identifier according to RFC6874
     *
     * @see http://tools.ietf.org/html/rfc6874#section-4
     *
     * @return static
     */
    public function withoutZoneIdentifier();

    /**
     * Return an instance with the Host appended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified host with the appended labels
     *
     * @param HierarchicalComponent|string $host the data to append
     *
     * @return static
     */
    public function appendHost($host);

    /**
     * Return an instance with the Host prepended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified host with the prepended labels
     *
     * @param HierarchicalComponent|string $host the data to prepend
     *
     * @return static
     */
    public function prependHost($host);

    /**
     * Return an instance with one of its Host label replaced
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the modified host with the replaced labels
     *
     * @param int                          $key   the Host label offset
     * @param HierarchicalComponent|string $value the data to inject
     *
     * @return static
     */
    public function replaceLabel($key, $value);

    /**
     * Return an instance without the submitted host labels
     *
     * This method MUST retain the state of the current instance, and return
     * an instance with the modified host without the selected labels
     *
     * @param callable|array $keys the list of label offsets to remove from the Host
     *                             if a callable is given it should filter the list
     *                             of offset to remove from the Host
     *
     * @return static
     */
    public function withoutLabels($keys);

    /**
     * Return an instance with the filtered host label
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the filtered labels
     *
     * @param callable $callable the callable should filter the list
     *                           of label to remain in the host
     *
     * @param int $flag Flag determining what argument are sent to callback
     *
     * @return static
     */
    public function filterHost(callable $callable, $flag = Collection::FILTER_USE_VALUE);

    /**
     * Retrieve the scheme specific part of the URI.
     *
     * If no specific part information is present, this method MUST return an empty
     * string.
     *
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getSchemeSpecificPart();

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
    public function __toString();

    /**
     * Return an array representation of the URI
     *
     * @return array
     */
    public function toArray();

    /**
     * Returns true if the URI is considered empty
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Returns whether two objects represents the same value
     * The comparison is based on the __toString method.
     * The following normalization is done prior to comparaison
     *
     *  - hosts if present are converted using the punycode algorithm
     *  - paths if present are normalized by removing dot segments
     *  - query strings if present are sorted using their offsets
     *
     * @param Uri|UriInterface $uri
     *
     * @return bool
     */
    public function sameValueAs($uri);
}
