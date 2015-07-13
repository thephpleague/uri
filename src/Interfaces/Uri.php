<?php
/**
 * League.Url (http://url.thephpleague.com)
 *
 * @link      https://github.com/thephpleague/url/
 * @copyright Copyright (c) 2013-2015 Ignace Nyamagana Butera
 * @license   https://github.com/thephpleague/url/blob/master/LICENSE (MIT License)
 * @version   4.0.0
 * @package   League.url
 */
namespace League\Uri\Interfaces;

use Psr\Http\Message\UriInterface;

/**
 * Value object representing a URL.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * @package League.url
 * @since   4.0.0
 * @see     https://tools.ietf.org/html/rfc3986
 *
 * @property-read Scheme         $scheme
 * @property-read UserInfo       $userInfo
 * @property-read Host           $host
 * @property-read Port           $port
 * @property-read Path           $path
 * @property-read Query          $query
 * @property-read Fragment       $fragment
 * @property-read SchemeRegistry $schemeRegistry
 */
interface Uri extends UriInterface
{
    /**
     * Return an array representation of the URI
     *
     * @return array
     */
    public function toArray();

    /**
     * Returns whether the standard port for the given scheme is used, when
     * the scheme is unknown or unsupported will the method return false
     *
     * @return bool
     */
    public function hasStandardPort();

    /**
     * Returns true if the URI is considered empty
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Returns whether two UriInterface represents the same value
     * The comparison is based on the __toString method.
     * The following normalization is done prior to comparaison
     *
     *  - hosts are converted using the punycode algorithm
     *  - query strings are sorted using their offsets
     *
     * @param UriInterface $uri
     *
     * @return bool
     */
    public function sameValueAs(UriInterface $uri);

    /**
     * Returns an instance resolve according to a given URL
     *
     * This method MUST retain the state of the current instance, and return
     * an instance resolved according to supplied URL
     *
     * @param Uri $rel the relative URL
     *
     * @return static
     *
     * @see https://tools.ietf.org/html/rfc3986#section-5.2
     */
    public function resolve(Uri $rel);

    /**
     * Return an instance with a new SchemeRegistry
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the new SchemeRegistry object
     *
     * @param SchemeRegistry $schemeRegistry
     *
     * @return static
     */
    public function withSchemeRegistry(SchemeRegistry $schemeRegistry);

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
     * @param  callable|int $sort a PHP sort flag constant or a comparaison function
     *                            which must return an integer less than, equal to,
     *                            or greater than zero if the first argument is
     *                            considered to be respectively less than, equal to,
     *                            or greater than the second.
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
     * @param int      $flag     Flag determining what argument are sent to callback
     *
     * @return static
     */
    public function filterQuery(callable $callable, $flag = Collection::FILTER_USE_VALUE);

    /**
     * Return an instance with its path appended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the appended path
     *
     * @param HierarchicalComponent|string $path the data to append
     *
     * @return static
     */
    public function appendPath($path);

    /**
     * Return an instance with its path prepended
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the prepended path
     *
     * @param HierarchicalComponent|string $path the data to prepend
     *
     * @return static
     */
    public function prependPath($path);

    /**
     * Return an instance with one of its Path segment replaced
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the path
     *
     * @param int                        $key the Path segment offset
     * @param HierarchicalComponent|string $value  the data to inject
     *
     * @return static
     */
    public function replaceSegment($key, $value);

    /**
     * Return an instance without the submitted path segments
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without the specified segments
     *
     * @param callable|array $keys the list of offsets to remove from the Path
     *                             if a callable is given it should filter the list
     *                             of offsets to remove from the Path
     *
     * @return static
     */
    public function withoutSegments($keys);

    /**
     * Return an instance without dot segments according to RFC3986 algorithm
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without dot segment according to RFC3986 algorithm
     *
     * @return static
     */
    public function normalize();

    /**
     * Return an instance without internal empty segments
     *
     * This method MUST retain the state of the current instance, and return
     * an instance without adjacent segment delimiters
     *
     * @return static
     */
    public function withoutEmptySegments();

    /**
     * Return an instance with the filtered path segments
     *
     * This method MUST retain the state of the current instance, and return
     * an instance containing the filtered segments
     *
     * @param callable $callable the callable should filter the list
     *                           of segment to remain in the path
     *
     * @param int      $flag     Flag determining what argument are sent to callback
     *
     * @return static
     */
    public function filterPath(callable $callable, $flag = Collection::FILTER_USE_VALUE);

    /**
     * Return an instance with the path extension updated
     *
     * This method MUST retain the state of the current instance, and return
     * an instance with the modified path extension
     *
     * @param string $extension the new path extension
     *
     * @return static
     */
    public function withExtension($extension);

    /**
     * Return an instance with the path containing a trailing slash
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the path component with a trailing slash
     *
     * if the path is an empty rootless path no slash is added
     *
     * @return static
     */
    public function withTrailingSlash();

    /**
     * Returns an instance without a trailing slash
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the path component without a trailing slash
     *
     * if the Path is the root path no slash is removed
     *
     * @return static
     */
    public function withoutTrailingSlash();

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
    public function toUnicode();

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
    public function toAscii();

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
     * @param int                          $key the Host label offset
     * @param HierarchicalComponent|string $value  the data to inject
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
     * @param int      $flag     Flag determining what argument are sent to callback
     *
     * @return static
     */
    public function filterHost(callable $callable, $flag = Collection::FILTER_USE_VALUE);
}
